<?php

namespace App\Http\Controllers;

use App\Models\BankAccount;
use App\Models\Category;
use App\Models\Counterparties;
use App\Models\Organization;
use App\Models\PaymentMethod;
use App\Models\Transaction;
use App\Models\TransactionGroup;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class TransactionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $organizationId = $this->currentOrganizationId();

        $type = $request->string('type')->toString();
        if (! in_array($type, ['income', 'expense'], true)) {
            $type = null;
        }

        $status = $request->string('status')->toString();
        if (! in_array($status, ['paid', 'payable'], true)) {
            $status = null;
        }

        $dateFrom = $request->string('date_from')->toString();
        $dateTo = $request->string('date_to')->toString();

        $parsedDateFrom = $this->parseDate($dateFrom);
        $parsedDateTo = $this->parseDate($dateTo);

        $baseQuery = Transaction::query()
            ->where('organization_id', $organizationId)
            ->with([
                'organization',
                'transactionGroup',
                'bankAccount',
                'paymentMethod',
                'counterparty',
                'category',
            ]);

        if ($type) {
            $baseQuery->where('type', $type);
        }

        if ($parsedDateFrom) {
            $baseQuery->whereDate('payment_date', '>=', $parsedDateFrom->toDateString());
        }

        if ($parsedDateTo) {
            $baseQuery->whereDate('payment_date', '<=', $parsedDateTo->toDateString());
        }

        $summaryQuery = clone $baseQuery;
        $transactionsQuery = clone $baseQuery;

        if ($status) {
            $transactionsQuery->where('payment_status', $status);
        }

        $transactions = $transactionsQuery
            ->orderByDesc('payment_date')
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        $totalAmount = (float) (clone $summaryQuery)->sum('amount');
        $paidAmount = (float) (clone $summaryQuery)->where('payment_status', 'paid')->sum('amount');
        $pendingAmount = (float) (clone $summaryQuery)->where('payment_status', 'payable')->sum('amount');

        $context = match ($type) {
            'income' => [
                'page_title' => 'Entradas',
                'page_description' => 'Registre todas as suas receitas e recebimentos',
                'new_button_label' => 'Nova Entrada',
                'date_label' => 'Recebimento',
                'counterparty_label' => 'Cliente',
                'source_label' => 'Fonte de Entrada',
                'type_label' => 'Entrada',
                'empty_text' => 'Nenhuma entrada encontrada.',
                'total_card_label' => 'Total Entradas',
                'paid_card_label' => 'Recebidas',
                'pending_card_label' => 'Pendentes',
                'status_paid_label' => 'Recebida',
            ],
            'expense' => [
                'page_title' => 'Saidas',
                'page_description' => 'Registre todos os seus gastos e pagamentos',
                'new_button_label' => 'Nova Saida',
                'date_label' => 'Pagamento',
                'counterparty_label' => 'Fornecedor',
                'source_label' => 'Fonte de Saida',
                'type_label' => 'Saida',
                'empty_text' => 'Nenhuma saida encontrada.',
                'total_card_label' => 'Total Saidas',
                'paid_card_label' => 'Pagas',
                'pending_card_label' => 'Pendentes',
                'status_paid_label' => 'Paga',
            ],
            default => [
                'page_title' => 'Lancamentos',
                'page_description' => 'Visualize e acompanhe todas as transacoes',
                'new_button_label' => 'Nova Transacao',
                'date_label' => 'Data',
                'counterparty_label' => 'Contraparte',
                'source_label' => 'Fonte',
                'type_label' => 'Tipo',
                'empty_text' => 'Nenhuma transacao encontrada.',
                'total_card_label' => 'Total Lancamentos',
                'paid_card_label' => 'Pagas',
                'pending_card_label' => 'Pendentes',
                'status_paid_label' => 'Paga',
            ],
        };

        $filters = [
            'type' => $type,
            'status' => $status,
            'date_from' => $parsedDateFrom?->toDateString(),
            'date_to' => $parsedDateTo?->toDateString(),
        ];

        $summary = [
            'total' => $totalAmount,
            'paid' => $paidAmount,
            'pending' => $pendingAmount,
        ];

        return view('transactions.index', compact(
            'transactions',
            'filters',
            'summary',
            'context'
        ));
    }

    public function launches(Request $request)
    {
        $organizationId = $this->currentOrganizationId();

        $type = $request->string('type')->toString();
        if (! in_array($type, ['income', 'expense'], true)) {
            $type = null;
        }

        $paymentStatus = $request->string('payment_status')->toString();
        if (! in_array($paymentStatus, ['paid', 'payable'], true)) {
            $paymentStatus = null;
        }

        $paymentDateFrom = $this->parseDate($request->string('payment_date_from')->toString());
        $paymentDateTo = $this->parseDate($request->string('payment_date_to')->toString());

        $transactions = Transaction::query()
            ->where('organization_id', $organizationId)
            ->with([
                'organization',
                'transactionGroup',
                'paymentMethod',
                'category',
            ])
            ->when($type, function ($query, $typeValue) {
                $query->where('type', $typeValue);
            })
            ->when($paymentStatus, function ($query, $statusValue) {
                $query->where('payment_status', $statusValue);
            })
            ->when($paymentDateFrom, function ($query, $fromDate) {
                $query->whereDate('payment_date', '>=', $fromDate->toDateString());
            })
            ->when($paymentDateTo, function ($query, $toDate) {
                $query->whereDate('payment_date', '<=', $toDate->toDateString());
            })
            ->orderByDesc('payment_date')
            ->orderByDesc('id')
            ->paginate(12)
            ->withQueryString();

        $filters = [
            'type' => $type,
            'payment_status' => $paymentStatus,
            'payment_date_from' => $paymentDateFrom?->toDateString(),
            'payment_date_to' => $paymentDateTo?->toDateString(),
        ];

        $typeOptions = [
            'income' => 'Receita',
            'expense' => 'Despesa',
        ];

        $paymentStatusOptions = [
            'paid' => 'Pago',
            'payable' => 'Pendente',
        ];

        $canManageFinance = auth()->check();

        return view('launches.index', compact(
            'transactions',
            'filters',
            'typeOptions',
            'paymentStatusOptions',
            'canManageFinance'
        ));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $organizationId = $this->currentOrganizationId();

        $defaultType = $request->string('type')->toString();
        if (! in_array($defaultType, ['income', 'expense'], true)) {
            $defaultType = null;
        }

        $transactionGroups = TransactionGroup::query()
            ->where('organization_id', $organizationId)
            ->with('organization')
            ->orderByDesc('id')
            ->get();
        $bankAccounts = BankAccount::query()
            ->where('organization_id', $organizationId)
            ->with('organization')
            ->orderBy('name')
            ->get();
        $paymentMethods = PaymentMethod::orderBy('name')->get();
        $counterparties = Counterparties::query()
            ->where('organization_id', $organizationId)
            ->with('organization')
            ->orderBy('name')
            ->get();
        $categories = Category::query()
            ->where('organization_id', $organizationId)
            ->with('organization')
            ->orderBy('name')
            ->get();

        return view('transactions.create', compact(
            'transactionGroups',
            'bankAccounts',
            'paymentMethods',
            'counterparties',
            'categories',
            'defaultType'
        ));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $organizationId = $this->currentOrganizationId();

        $validated = $request->validate([
            'transaction_group_id' => [
                'required',
                'integer',
                Rule::exists('transaction_group', 'id')->where(fn ($query) => $query->where('organization_id', $organizationId)),
            ],
            'bank_account_id' => [
                'required',
                'integer',
                Rule::exists('bank_account', 'id')->where(fn ($query) => $query->where('organization_id', $organizationId)),
            ],
            'payment_method_id' => ['required', 'integer', 'exists:payment_method,id'],
            'counterparty_id' => [
                'required',
                'integer',
                Rule::exists('counterparties', 'id')->where(fn ($query) => $query->where('organization_id', $organizationId)),
                function (string $attribute, mixed $value, \Closure $fail) use ($request, $organizationId): void {
                    $transactionType = $request->string('type')->toString();
                    $expectedCounterpartyType = $transactionType === 'income'
                        ? 'client'
                        : ($transactionType === 'expense' ? 'supplier' : null);

                    if (! $expectedCounterpartyType) {
                        return;
                    }

                    $isValid = Counterparties::query()
                        ->where('organization_id', $organizationId)
                        ->whereKey((int) $value)
                        ->where('type', $expectedCounterpartyType)
                        ->exists();

                    if (! $isValid) {
                        $fail('Selecione um '.($expectedCounterpartyType === 'client' ? 'cliente' : 'fornecedor').' valido para o tipo da transacao.');
                    }
                },
            ],
            'category_id' => [
                'required',
                'integer',
                Rule::exists('category', 'id')->where(fn ($query) => $query->where('organization_id', $organizationId)),
                function (string $attribute, mixed $value, \Closure $fail) use ($request, $organizationId): void {
                    $transactionType = $request->string('type')->toString();

                    if (! in_array($transactionType, ['income', 'expense'], true)) {
                        return;
                    }

                    $isValid = Category::query()
                        ->where('organization_id', $organizationId)
                        ->whereKey((int) $value)
                        ->where('type', $transactionType)
                        ->exists();

                    if (! $isValid) {
                        $fail('Selecione uma categoria valida para o tipo da transacao.');
                    }
                },
            ],
            'installment_number' => ['required', 'integer', 'min:1'],
            'expected_payment_date' => ['required', 'date'],
            'payment_date' => ['required', 'date'],
            'amount' => ['required', 'numeric', 'min:0'],
            'payment_status' => ['required', 'in:paid,payable'],
            'expense_type' => ['required', 'in:professional,personal'],
            'type' => ['required', 'in:income,expense'],
        ]);

        $validated['organization_id'] = $organizationId;

        Transaction::create($validated);

        return redirect()
            ->route('transactions.index', ['type' => $validated['type']])
            ->with('success', 'Transacao criada com sucesso.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $organizationId = $this->currentOrganizationId();

        $transaction = Transaction::query()
            ->where('organization_id', $organizationId)
            ->with([
                'organization',
                'transactionGroup',
                'bankAccount',
                'paymentMethod',
                'counterparty',
                'category',
            ])->findOrFail($id);

        return view('transactions.show', compact('transaction'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $organizationId = $this->currentOrganizationId();

        $transaction = Transaction::query()
            ->where('organization_id', $organizationId)
            ->findOrFail($id);
        $organizations = Organization::query()
            ->whereKey($organizationId)
            ->orderBy('name')
            ->get();
        $transactionGroups = TransactionGroup::query()
            ->where('organization_id', $organizationId)
            ->with('organization')
            ->orderByDesc('id')
            ->get();
        $bankAccounts = BankAccount::query()
            ->where('organization_id', $organizationId)
            ->with('organization')
            ->orderBy('name')
            ->get();
        $paymentMethods = PaymentMethod::orderBy('name')->get();
        $counterparties = Counterparties::query()
            ->where('organization_id', $organizationId)
            ->with('organization')
            ->orderBy('name')
            ->get();
        $categories = Category::query()
            ->where('organization_id', $organizationId)
            ->with('organization')
            ->orderBy('name')
            ->get();

        return view('transactions.edit', compact(
            'transaction',
            'organizations',
            'transactionGroups',
            'bankAccounts',
            'paymentMethods',
            'counterparties',
            'categories'
        ));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $organizationId = $this->currentOrganizationId();

        $transaction = Transaction::query()
            ->where('organization_id', $organizationId)
            ->findOrFail($id);

        $validated = $request->validate([
            'transaction_group_id' => [
                'required',
                'integer',
                Rule::exists('transaction_group', 'id')->where(fn ($query) => $query->where('organization_id', $organizationId)),
            ],
            'bank_account_id' => [
                'required',
                'integer',
                Rule::exists('bank_account', 'id')->where(fn ($query) => $query->where('organization_id', $organizationId)),
            ],
            'payment_method_id' => ['required', 'integer', 'exists:payment_method,id'],
            'counterparty_id' => [
                'required',
                'integer',
                Rule::exists('counterparties', 'id')->where(fn ($query) => $query->where('organization_id', $organizationId)),
                function (string $attribute, mixed $value, \Closure $fail) use ($request, $organizationId): void {
                    $transactionType = $request->string('type')->toString();
                    $expectedCounterpartyType = $transactionType === 'income'
                        ? 'client'
                        : ($transactionType === 'expense' ? 'supplier' : null);

                    if (! $expectedCounterpartyType) {
                        return;
                    }

                    $isValid = Counterparties::query()
                        ->where('organization_id', $organizationId)
                        ->whereKey((int) $value)
                        ->where('type', $expectedCounterpartyType)
                        ->exists();

                    if (! $isValid) {
                        $fail('Selecione um '.($expectedCounterpartyType === 'client' ? 'cliente' : 'fornecedor').' valido para o tipo da transacao.');
                    }
                },
            ],
            'category_id' => [
                'required',
                'integer',
                Rule::exists('category', 'id')->where(fn ($query) => $query->where('organization_id', $organizationId)),
                function (string $attribute, mixed $value, \Closure $fail) use ($request, $organizationId): void {
                    $transactionType = $request->string('type')->toString();

                    if (! in_array($transactionType, ['income', 'expense'], true)) {
                        return;
                    }

                    $isValid = Category::query()
                        ->where('organization_id', $organizationId)
                        ->whereKey((int) $value)
                        ->where('type', $transactionType)
                        ->exists();

                    if (! $isValid) {
                        $fail('Selecione uma categoria valida para o tipo da transacao.');
                    }
                },
            ],
            'installment_number' => ['required', 'integer', 'min:1'],
            'expected_payment_date' => ['required', 'date'],
            'payment_date' => ['required', 'date'],
            'amount' => ['required', 'numeric', 'min:0'],
            'payment_status' => ['required', 'in:paid,payable'],
            'expense_type' => ['required', 'in:professional,personal'],
            'type' => ['required', 'in:income,expense'],
        ]);

        $validated['organization_id'] = $organizationId;

        $transaction->update($validated);

        return redirect()
            ->route('transactions.index', ['type' => $validated['type']])
            ->with('success', 'Transacao atualizada com sucesso.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $organizationId = $this->currentOrganizationId();

        $transaction = Transaction::query()
            ->where('organization_id', $organizationId)
            ->findOrFail($id);
        $type = $transaction->type;
        $transaction->delete();

        return redirect()
            ->route('transactions.index', ['type' => $type])
            ->with('success', 'Transacao removida com sucesso.');
    }

    private function parseDate(?string $value): ?Carbon
    {
        if (! $value) {
            return null;
        }

        try {
            return Carbon::parse($value);
        } catch (\Throwable $exception) {
            return null;
        }
    }

    private function currentOrganizationId(): int
    {
        $organizationId = (int) Auth::user()?->organization_id;

        abort_if($organizationId <= 0, 403, 'Usuario sem organizacao vinculada.');

        return $organizationId;
    }
}
