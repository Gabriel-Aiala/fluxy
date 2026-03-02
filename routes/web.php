<?php

use App\Http\Controllers\BankAccountController;
use App\Http\Controllers\BankController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ControlController;
use App\Http\Controllers\CounterpartiesController;
use App\Http\Controllers\DailyFlowController;
use App\Http\Controllers\PaymentMethodController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\QuickCreateController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\TransactionGroupController;
use App\Models\Transaction;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;

Route::redirect('/', '/login');

Route::get('/dashboard', function () {
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
    $currentMonth = now()->startOfMonth();
    $calendarStartOffset = (int) $currentMonth->copy()->startOfMonth()->dayOfWeekIso - 1;
    $calendarMonthLabel = ($monthFullNames[(int) $currentMonth->format('n')] ?? $currentMonth->format('m')).' de '.$currentMonth->format('Y');

    $calendarDays = collect(range(1, $currentMonth->daysInMonth))->map(function (int $day) use ($currentMonth) {
        $dayDate = $currentMonth->copy()->day($day);

        return [
            'day' => $day,
            'is_today' => $dayDate->isSameDay(now()),
            'has_receivable' => false,
            'has_payable' => false,
            'receivable_total' => 0.0,
            'payable_total' => 0.0,
        ];
    });

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

            $pendingTransactions = (clone $transactionScope)
                ->where('payment_status', 'payable')
                ->whereBetween('expected_payment_date', [$monthStart, $monthEnd])
                ->get(['type', 'amount', 'expected_payment_date']);

            $calendarByDay = $pendingTransactions->reduce(function (array $carry, Transaction $transaction) {
                if (! $transaction->expected_payment_date) {
                    return $carry;
                }

                try {
                    $day = Carbon::parse($transaction->expected_payment_date)->day;
                } catch (\Throwable $exception) {
                    return $carry;
                }

                if (! isset($carry[$day])) {
                    $carry[$day] = [
                        'receivable_total' => 0.0,
                        'payable_total' => 0.0,
                    ];
                }

                if ($transaction->type === 'income') {
                    $carry[$day]['receivable_total'] += (float) $transaction->amount;
                }

                if ($transaction->type === 'expense') {
                    $carry[$day]['payable_total'] += (float) $transaction->amount;
                }

                return $carry;
            }, []);

            $calendarDays = $calendarDays->map(function (array $dayData) use ($calendarByDay) {
                $dayTotals = $calendarByDay[$dayData['day']] ?? null;
                if (! $dayTotals) {
                    return $dayData;
                }

                $receivableTotal = (float) ($dayTotals['receivable_total'] ?? 0.0);
                $payableTotal = (float) ($dayTotals['payable_total'] ?? 0.0);

                return [
                    ...$dayData,
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
        }
    }

    return view('dashboard', compact(
        'totals',
        'chart',
        'incomeCategoryTotals',
        'expenseCategoryTotals',
        'calendarDays',
        'calendarStartOffset',
        'calendarMonthLabel'
    ));
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::prefix('quick-create')->name('quick-create.')->group(function () {
        Route::post('/transaction-groups', [QuickCreateController::class, 'transactionGroup'])->name('transaction-groups');
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
    Route::resource('transaction-groups', TransactionGroupController::class);
    Route::resource('transactions', TransactionController::class);
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
