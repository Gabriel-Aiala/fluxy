<?php

use App\Http\Controllers\BankAccountController;
use App\Http\Controllers\BankController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ControlController;
use App\Http\Controllers\CounterpartiesController;
use App\Http\Controllers\DailyFlowController;
use App\Http\Controllers\PaymentMethodController;
use App\Http\Controllers\QuickCreateController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\TransactionGroupController;
use App\Models\Transaction;
use Illuminate\Support\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;

Route::redirect('/', '/login');

Route::get('/dashboard', function (Request $request) {
    $monthNames = [
        1 => 'JAN',
        2 => 'FEV',
        3 => 'MAR',
        4 => 'ABR',
        5 => 'MAI',
        6 => 'JUN',
        7 => 'JUL',
        8 => 'AGO',
        9 => 'SET',
        10 => 'OUT',
        11 => 'NOV',
        12 => 'DEZ',
    ];

    $monthFullNames = [
        1 => 'Janeiro',
        2 => 'Fevereiro',
        3 => 'Marco',
        4 => 'Abril',
        5 => 'Maio',
        6 => 'Junho',
        7 => 'Julho',
        8 => 'Agosto',
        9 => 'Setembro',
        10 => 'Outubro',
        11 => 'Novembro',
        12 => 'Dezembro',
    ];

    $organizationId = (int) (auth()->user()?->organization_id ?? 0);

    $requestedMonth = $request->string('month')->toString();
    $currentMonth = now()->startOfMonth();
    if (preg_match('/^\d{4}-\d{2}$/', $requestedMonth) === 1) {
        try {
            $currentMonth = Carbon::createFromFormat('Y-m', $requestedMonth)->startOfMonth();
        } catch (\Throwable $exception) {
            $currentMonth = now()->startOfMonth();
        }
    }

    $calendarStartOffset = (int) $currentMonth->copy()->startOfMonth()->dayOfWeekIso - 1;
    $calendarMonthLabel = ($monthFullNames[(int) $currentMonth->format('n')] ?? $currentMonth->format('m')).' de '.$currentMonth->format('Y');
    $calendarSelectedMonth = $currentMonth->format('Y-m');
    $calendarPrevMonth = $currentMonth->copy()->subMonthNoOverflow()->format('Y-m');
    $calendarNextMonth = $currentMonth->copy()->addMonthNoOverflow()->format('Y-m');

    $calendarDays = collect(range(1, $currentMonth->daysInMonth))->map(function (int $day) use ($currentMonth) {
        $dayDate = $currentMonth->copy()->day($day);

        return [
            'day' => $day,
            'iso_date' => $dayDate->toDateString(),
            'is_today' => $dayDate->isSameDay(now()),
            'has_transactions' => false,
            'has_done' => false,
            'has_planned' => false,
            'has_receivable' => false,
            'has_payable' => false,
            'receivable_total' => 0.0,
            'payable_total' => 0.0,
        ];
    });
    $calendarDayDetails = [];

    $periods = collect([
        now()->subMonthNoOverflow(),
        now(),
    ]);

    $totals = [
        'income' => 0.0,
        'expense' => 0.0,
        'balance' => 0.0,
        'margin' => 0.0,
        'to_receive' => 0.0,
        'to_pay' => 0.0,
    ];

    $chart = $periods->map(function ($period) use ($monthNames) {
        return [
            'label' => $monthNames[(int) $period->format('n')],
            'income' => 0.0,
            'expense' => 0.0,
        ];
    });

    $incomeCategoryTotals = collect();
    $expenseCategoryTotals = collect();

    if (Schema::hasTable('transaction')) {
        try {
            $transactionScope = Transaction::query();
            if ($organizationId > 0) {
                $transactionScope->where('organization_id', $organizationId);
            }

            $totals['income'] = (float) (clone $transactionScope)
                ->where('type', 'income')
                ->where('payment_status', 'paid')
                ->sum('amount');

            $totals['expense'] = (float) (clone $transactionScope)
                ->where('type', 'expense')
                ->where('payment_status', 'paid')
                ->sum('amount');

            $totals['to_receive'] = (float) (clone $transactionScope)
                ->where('type', 'income')
                ->where('payment_status', 'payable')
                ->sum('amount');

            $totals['to_pay'] = (float) (clone $transactionScope)
                ->where('type', 'expense')
                ->where('payment_status', 'payable')
                ->sum('amount');

            $totals['balance'] = $totals['income'] - $totals['expense'];
            $totals['margin'] = $totals['income'] > 0
                ? ($totals['balance'] / $totals['income']) * 100
                : 0.0;

            $chart = $periods->map(function ($period) use ($monthNames, $transactionScope) {
                return [
                    'label' => $monthNames[(int) $period->format('n')],
                    'income' => (float) (clone $transactionScope)
                        ->whereYear('payment_date', (int) $period->format('Y'))
                        ->whereMonth('payment_date', (int) $period->format('n'))
                        ->where('type', 'income')
                        ->sum('amount'),
                    'expense' => (float) (clone $transactionScope)
                        ->whereYear('payment_date', (int) $period->format('Y'))
                        ->whereMonth('payment_date', (int) $period->format('n'))
                        ->where('type', 'expense')
                        ->sum('amount'),
                ];
            });

            if (Schema::hasTable('category')) {
                $incomeCategoryTotals = (clone $transactionScope)
                    ->where('type', 'income')
                    ->with('category:id,name')
                    ->selectRaw('category_id, SUM(amount) as total_amount')
                    ->groupBy('category_id')
                    ->orderByDesc('total_amount')
                    ->limit(8)
                    ->get()
                    ->map(function ($item) {
                        return [
                            'category' => $item->category->name ?? 'Sem categoria',
                            'total' => (float) $item->total_amount,
                        ];
                    })
                    ->values();

                $expenseCategoryTotals = (clone $transactionScope)
                    ->where('type', 'expense')
                    ->with('category:id,name')
                    ->selectRaw('category_id, SUM(amount) as total_amount')
                    ->groupBy('category_id')
                    ->orderByDesc('total_amount')
                    ->limit(8)
                    ->get()
                    ->map(function ($item) {
                        return [
                            'category' => $item->category->name ?? 'Sem categoria',
                            'total' => (float) $item->total_amount,
                        ];
                    })
                    ->values();
            }

            $monthStart = $currentMonth->copy()->startOfMonth()->toDateString();
            $monthEnd = $currentMonth->copy()->endOfMonth()->toDateString();

            $calendarTransactions = (clone $transactionScope)
                ->with([
                    'category:id,name',
                    'counterparty:id,name',
                    'bankAccount:id,name',
                    'paymentMethod:id,name',
                ])
                ->where(function ($query) use ($monthStart, $monthEnd) {
                    $query
                        ->where(function ($paidQuery) use ($monthStart, $monthEnd) {
                            $paidQuery
                                ->where('payment_status', 'paid')
                                ->whereBetween('payment_date', [$monthStart, $monthEnd]);
                        })
                        ->orWhere(function ($payableQuery) use ($monthStart, $monthEnd) {
                            $payableQuery
                                ->where('payment_status', 'payable')
                                ->whereBetween('expected_payment_date', [$monthStart, $monthEnd]);
                        });
                })
                ->get([
                    'id',
                    'type',
                    'payment_status',
                    'amount',
                    'installment_number',
                    'payment_date',
                    'expected_payment_date',
                    'category_id',
                    'counterparty_id',
                    'bank_account_id',
                    'payment_method_id',
                ]);

            $calendarDayDetails = $calendarTransactions->reduce(function (array $carry, Transaction $transaction) {
                $isDone = $transaction->payment_status === 'paid';
                $isIncome = $transaction->type === 'income';
                $referenceDate = $isDone
                    ? $transaction->payment_date?->toDateString()
                    : $transaction->expected_payment_date?->toDateString();

                if (! $referenceDate) {
                    return $carry;
                }

                if (! isset($carry[$referenceDate])) {
                    $carry[$referenceDate] = [
                        'has_done' => false,
                        'has_planned' => false,
                        'done_income_total' => 0.0,
                        'done_expense_total' => 0.0,
                        'planned_income_total' => 0.0,
                        'planned_expense_total' => 0.0,
                        'transactions' => [],
                    ];
                }

                $amount = (float) $transaction->amount;
                if ($isDone) {
                    $carry[$referenceDate]['has_done'] = true;
                    if ($isIncome) {
                        $carry[$referenceDate]['done_income_total'] += $amount;
                    } else {
                        $carry[$referenceDate]['done_expense_total'] += $amount;
                    }
                } else {
                    $carry[$referenceDate]['has_planned'] = true;
                    if ($isIncome) {
                        $carry[$referenceDate]['planned_income_total'] += $amount;
                    } else {
                        $carry[$referenceDate]['planned_expense_total'] += $amount;
                    }
                }

                $statusLabel = match (true) {
                    $isDone && $isIncome => 'Recebido',
                    $isDone && ! $isIncome => 'Pago',
                    ! $isDone && $isIncome => 'A receber',
                    default => 'A pagar',
                };

                $carry[$referenceDate]['transactions'][] = [
                    'id' => (int) $transaction->id,
                    'type' => $transaction->type,
                    'payment_status' => $transaction->payment_status,
                    'amount' => round($amount, 2),
                    'installment_number' => (int) $transaction->installment_number,
                    'reference_date' => $referenceDate,
                    'status_label_ptbr' => $statusLabel,
                    'type_label_ptbr' => $isIncome ? 'Receita' : 'Despesa',
                    'category_name' => $transaction->category->name ?? '-',
                    'counterparty_name' => $transaction->counterparty->name ?? '-',
                    'bank_account_name' => $transaction->bankAccount->name ?? '-',
                    'payment_method_name' => $transaction->paymentMethod->name ?? '-',
                    'edit_url' => route('transactions.edit', $transaction->id),
                ];

                return $carry;
            }, []);

            $calendarDayDetails = collect($calendarDayDetails)
                ->map(function (array $dayDetails): array {
                    usort($dayDetails['transactions'], function (array $first, array $second): int {
                        $firstPriority = $first['payment_status'] === 'paid' ? 0 : 1;
                        $secondPriority = $second['payment_status'] === 'paid' ? 0 : 1;

                        if ($firstPriority === $secondPriority) {
                            return $first['id'] <=> $second['id'];
                        }

                        return $firstPriority <=> $secondPriority;
                    });

                    return $dayDetails;
                })
                ->all();

            $calendarDays = $calendarDays->map(function (array $dayData) use ($calendarDayDetails) {
                $dayDetails = $calendarDayDetails[$dayData['iso_date']] ?? null;
                if (! $dayDetails) {
                    return $dayData;
                }

                $receivableTotal = (float) ($dayDetails['planned_income_total'] ?? 0.0);
                $payableTotal = (float) ($dayDetails['planned_expense_total'] ?? 0.0);

                return [
                    ...$dayData,
                    'has_transactions' => true,
                    'has_done' => (bool) ($dayDetails['has_done'] ?? false),
                    'has_planned' => (bool) ($dayDetails['has_planned'] ?? false),
                    'has_receivable' => $receivableTotal > 0,
                    'has_payable' => $payableTotal > 0,
                    'receivable_total' => $receivableTotal,
                    'payable_total' => $payableTotal,
                ];
            });
        } catch (\Throwable $exception) {
            $totals = [
                'income' => 0.0,
                'expense' => 0.0,
                'balance' => 0.0,
                'margin' => 0.0,
                'to_receive' => 0.0,
                'to_pay' => 0.0,
            ];
            $chart = $periods->map(function ($period) use ($monthNames) {
                return [
                    'label' => $monthNames[(int) $period->format('n')],
                    'income' => 0.0,
                    'expense' => 0.0,
                ];
            });
            $incomeCategoryTotals = collect();
            $expenseCategoryTotals = collect();
            $calendarDayDetails = [];
        }
    }

    return view('dashboard', compact(
        'totals',
        'chart',
        'incomeCategoryTotals',
        'expenseCategoryTotals',
        'calendarDays',
        'calendarDayDetails',
        'calendarStartOffset',
        'calendarMonthLabel',
        'calendarSelectedMonth',
        'calendarPrevMonth',
        'calendarNextMonth'
    ));
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::prefix('quick-create')->name('quick-create.')->group(function () {
        Route::post('/bank-accounts', [QuickCreateController::class, 'bankAccount'])->name('bank-accounts');
        Route::post('/payment-methods', [QuickCreateController::class, 'paymentMethod'])->name('payment-methods');
        Route::post('/counterparties', [QuickCreateController::class, 'counterparty'])->name('counterparties');
        Route::post('/categories', [QuickCreateController::class, 'category'])->name('categories');
    });

    Route::get('/bank', [BankController::class, 'index'])->name('bank.index');
    Route::get('/control', [ControlController::class, 'index'])->name('control.index');
    Route::get('/daily-flow', [DailyFlowController::class, 'index'])->name('daily-flow.index');
    Route::get('/launches', [TransactionController::class, 'launches'])->name('launches.index');
    Route::get('/registers', [RegisterController::class, 'index'])->name('registers.index');
    Route::resource('bank-accounts', BankAccountController::class);
    Route::resource('payment-methods', PaymentMethodController::class);
    Route::resource('categories', CategoryController::class);
    Route::resource('counterparties', CounterpartiesController::class);
    Route::resource('transaction-groups', TransactionGroupController::class)->only(['index', 'show']);
    Route::resource('transactions', TransactionController::class);
});

require __DIR__.'/auth.php';
