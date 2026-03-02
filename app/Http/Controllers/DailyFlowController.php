<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;

class DailyFlowController extends Controller
{
    public function index(Request $request)
    {
        $selectedMonthInput = $request->string('month')->toString();
        $selectedMonth = $this->parseMonth($selectedMonthInput) ?? now()->startOfMonth();

        $monthTabs = [
            ['number' => 1, 'short' => 'JAN'],
            ['number' => 2, 'short' => 'FEV'],
            ['number' => 3, 'short' => 'MAR'],
            ['number' => 4, 'short' => 'ABR'],
            ['number' => 5, 'short' => 'MAI'],
            ['number' => 6, 'short' => 'JUN'],
            ['number' => 7, 'short' => 'JUL'],
            ['number' => 8, 'short' => 'AGO'],
            ['number' => 9, 'short' => 'SET'],
            ['number' => 10, 'short' => 'OUT'],
            ['number' => 11, 'short' => 'NOV'],
            ['number' => 12, 'short' => 'DEZ'],
        ];

        $summary = [
            'previous_balance' => 0.0,
            'received' => 0.0,
            'paid' => 0.0,
            'final_balance' => 0.0,
        ];

        $rows = [];

        if (Schema::hasTable('transaction')) {
            $monthStart = $selectedMonth->copy()->startOfMonth();
            $monthEnd = $selectedMonth->copy()->endOfMonth();

            $previousIncome = (float) Transaction::query()
                ->where('type', 'income')
                ->where('payment_status', 'paid')
                ->whereDate('payment_date', '<', $monthStart->toDateString())
                ->sum('amount');

            $previousExpense = (float) Transaction::query()
                ->where('type', 'expense')
                ->where('payment_status', 'paid')
                ->whereDate('payment_date', '<', $monthStart->toDateString())
                ->sum('amount');

            $summary['previous_balance'] = $previousIncome - $previousExpense;

            $summary['received'] = (float) Transaction::query()
                ->where('type', 'income')
                ->where('payment_status', 'paid')
                ->whereBetween('payment_date', [$monthStart->toDateString(), $monthEnd->toDateString()])
                ->sum('amount');

            $summary['paid'] = (float) Transaction::query()
                ->where('type', 'expense')
                ->where('payment_status', 'paid')
                ->whereBetween('payment_date', [$monthStart->toDateString(), $monthEnd->toDateString()])
                ->sum('amount');

            $summary['final_balance'] = $summary['previous_balance'] + $summary['received'] - $summary['paid'];

            $receivedByDay = Transaction::query()
                ->selectRaw('DAY(payment_date) as day_number, SUM(amount) as total_amount')
                ->where('type', 'income')
                ->where('payment_status', 'paid')
                ->whereBetween('payment_date', [$monthStart->toDateString(), $monthEnd->toDateString()])
                ->groupByRaw('DAY(payment_date)')
                ->pluck('total_amount', 'day_number')
                ->map(fn ($value) => (float) $value)
                ->all();

            $paidByDay = Transaction::query()
                ->selectRaw('DAY(payment_date) as day_number, SUM(amount) as total_amount')
                ->where('type', 'expense')
                ->where('payment_status', 'paid')
                ->whereBetween('payment_date', [$monthStart->toDateString(), $monthEnd->toDateString()])
                ->groupByRaw('DAY(payment_date)')
                ->pluck('total_amount', 'day_number')
                ->map(fn ($value) => (float) $value)
                ->all();

            $receivableByDay = Transaction::query()
                ->selectRaw('DAY(expected_payment_date) as day_number, SUM(amount) as total_amount')
                ->where('type', 'income')
                ->where('payment_status', 'payable')
                ->whereBetween('expected_payment_date', [$monthStart->toDateString(), $monthEnd->toDateString()])
                ->groupByRaw('DAY(expected_payment_date)')
                ->pluck('total_amount', 'day_number')
                ->map(fn ($value) => (float) $value)
                ->all();

            $payableByDay = Transaction::query()
                ->selectRaw('DAY(expected_payment_date) as day_number, SUM(amount) as total_amount')
                ->where('type', 'expense')
                ->where('payment_status', 'payable')
                ->whereBetween('expected_payment_date', [$monthStart->toDateString(), $monthEnd->toDateString()])
                ->groupByRaw('DAY(expected_payment_date)')
                ->pluck('total_amount', 'day_number')
                ->map(fn ($value) => (float) $value)
                ->all();

            $runningBalance = $summary['previous_balance'];
            $daysInMonth = $selectedMonth->daysInMonth;

            for ($day = 1; $day <= $daysInMonth; $day++) {
                $received = (float) ($receivedByDay[$day] ?? 0);
                $paid = (float) ($paidByDay[$day] ?? 0);
                $application = $received - $paid;
                $runningBalance += $application;

                $receivable = (float) ($receivableByDay[$day] ?? 0);
                $payable = (float) ($payableByDay[$day] ?? 0);

                $rows[] = [
                    'day' => $day,
                    'received' => $received,
                    'paid' => $paid,
                    'application' => $application,
                    'current_balance' => $runningBalance,
                    'receivable' => $receivable,
                    'payable' => $payable,
                    'predicted_balance' => $runningBalance + $receivable - $payable,
                ];
            }
        }

        return view('daily-flow.index', [
            'monthTabs' => $monthTabs,
            'selectedMonth' => $selectedMonth,
            'summary' => $summary,
            'rows' => $rows,
        ]);
    }

    private function parseMonth(?string $month): ?Carbon
    {
        if (! $month) {
            return null;
        }

        if (! preg_match('/^\d{4}-\d{2}$/', $month)) {
            return null;
        }

        try {
            return Carbon::createFromFormat('Y-m', $month)->startOfMonth();
        } catch (\Throwable $exception) {
            return null;
        }
    }
}
