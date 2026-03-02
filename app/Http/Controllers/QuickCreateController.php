<?php

namespace App\Http\Controllers;

use App\Models\BankAccount;
use App\Models\Category;
use App\Models\Counterparties;
use App\Models\PaymentMethod;
use App\Models\TransactionGroup;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class QuickCreateController extends Controller
{
    public function transactionGroup(Request $request): JsonResponse
    {
        $organizationId = $this->currentOrganizationId();

        $validated = $request->validate([
            'type' => ['required', 'in:income,expense'],
            'description' => ['nullable', 'string', 'max:255'],
            'occurred_on' => ['required', 'date'],
            'customer_installments' => ['required', 'integer', 'min:1'],
            'flow_installments' => ['required', 'integer', 'min:1'],
            'anticipation' => ['nullable', 'boolean'],
        ]);

        $validated['anticipation'] = $request->boolean('anticipation');
        $validated['organization_id'] = $organizationId;

        $transactionGroup = TransactionGroup::create($validated);
        $transactionGroup->loadMissing('organization');

        $label = sprintf(
            '#%d - %s - %s',
            $transactionGroup->id,
            $transactionGroup->type,
            $transactionGroup->organization->name ?? '-'
        );

        return $this->created('transaction_group', $transactionGroup->id, $label);
    }

    public function bankAccount(Request $request): JsonResponse
    {
        $organizationId = $this->currentOrganizationId();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        $validated['organization_id'] = $organizationId;

        $bankAccount = BankAccount::create($validated);
        $bankAccount->loadMissing('organization');

        $label = sprintf(
            '%s - %s',
            $bankAccount->name,
            $bankAccount->organization->name ?? '-'
        );

        return $this->created('bank_account', $bankAccount->id, $label);
    }

    public function paymentMethod(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('payment_method', 'name')],
        ]);

        $paymentMethod = PaymentMethod::create($validated);

        return $this->created('payment_method', $paymentMethod->id, $paymentMethod->name);
    }

    public function counterparty(Request $request): JsonResponse
    {
        $organizationId = $this->currentOrganizationId();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'in:client,supplier'],
        ]);

        $validated['organization_id'] = $organizationId;

        $counterparty = Counterparties::create($validated);
        $counterparty->loadMissing('organization');

        $label = sprintf(
            '%s (%s) - %s',
            $counterparty->name,
            $counterparty->type,
            $counterparty->organization->name ?? '-'
        );

        return $this->created('counterparty', $counterparty->id, $label, [
            'counterparty_type' => $counterparty->type,
        ]);
    }

    public function category(Request $request): JsonResponse
    {
        $organizationId = $this->currentOrganizationId();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'in:income,expense'],
            'cost_type' => [
                Rule::requiredIf(fn () => $request->string('type')->toString() === 'expense'),
                'nullable',
                Rule::in(['fixed', 'variable', 'income']),
                function (string $attribute, mixed $value, \Closure $fail) use ($request): void {
                    $type = $request->string('type')->toString();

                    if ($type === 'expense' && ! in_array($value, ['fixed', 'variable'], true)) {
                        $fail('Tipo de custo invalido para despesa.');
                    }
                },
            ],
        ]);

        if ($validated['type'] === 'income') {
            $validated['cost_type'] = 'income';
        }

        $validated['organization_id'] = $organizationId;

        $category = Category::create($validated);
        $category->loadMissing('organization');

        $label = $category->name;

        return $this->created('category', $category->id, $label, [
            'category_type' => $category->type,
        ]);
    }

    private function created(string $model, int $id, string $label, array $extra = []): JsonResponse
    {
        return response()->json([
            'model' => $model,
            'id' => $id,
            'label' => $label,
            ...$extra,
        ], 201);
    }

    private function currentOrganizationId(): int
    {
        $organizationId = (int) Auth::user()?->organization_id;

        abort_if($organizationId <= 0, 403, 'Usuario sem organizacao vinculada.');

        return $organizationId;
    }
}
