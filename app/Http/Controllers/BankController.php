<?php

namespace App\Http\Controllers;

use App\Models\BankAccount;
use App\Models\PaymentMethod;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;

class BankController extends Controller
{
    public function index(Request $request)
    {
        $selectedMonth = $this->parseMonth($request->string('month')->toString()) ?? now()->startOfMonth();
        $monthStart = $selectedMonth->copy()->startOfMonth();
        $monthEnd = $selectedMonth->copy()->endOfMonth();

        $paymentMethods = PaymentMethod::query()
            ->orderBy('name')
            ->get(['id', 'name']);

        $bankAccounts = BankAccount::query()
            ->orderBy('name')
            ->get(['id', 'name']);

        $totals = [
            'income' => 0.0,
            'expense' => 0.0,
            'balance' => 0.0,
        ];

        $incomeTable = [
            'rows' => [],
            'columnTotals' => [],
            'grandTotal' => 0.0,
        ];

        $expenseTable = [
            'rows' => [],
            'columnTotals' => [],
            'grandTotal' => 0.0,
        ];

        if (Schema::hasTable('transaction')) {
            $totals['income'] = (float) Transaction::query()
                ->where('type', 'income')
                ->whereBetween('payment_date', [$monthStart->toDateString(), $monthEnd->toDateString()])
                ->sum('amount');

            $totals['expense'] = (float) Transaction::query()
                ->where('type', 'expense')
                ->whereBetween('payment_date', [$monthStart->toDateString(), $monthEnd->toDateString()])
                ->sum('amount');

            $totals['balance'] = $totals['income'] - $totals['expense'];

            $grouped = Transaction::query()
                ->selectRaw('bank_account_id, payment_method_id, type, SUM(amount) as total_amount')
                ->whereBetween('payment_date', [$monthStart->toDateString(), $monthEnd->toDateString()])
                ->groupBy('bank_account_id')
                ->groupBy('payment_method_id')
                ->groupBy('type')
                ->get();

            $totalsMap = [];
            foreach ($grouped as $item) {
                $totalsMap[(string) $item->type][(int) $item->bank_account_id][(int) $item->payment_method_id] = (float) $item->total_amount;
            }

            $incomeTable = $this->buildTable($bankAccounts, $paymentMethods, $totalsMap['income'] ?? [], 'income');
            $expenseTable = $this->buildTable($bankAccounts, $paymentMethods, $totalsMap['expense'] ?? [], 'expense');
        }

        return view('bank.index', compact(
            'selectedMonth',
            'paymentMethods',
            'totals',
            'incomeTable',
            'expenseTable'
        ));
    }

    private function buildTable($bankAccounts, $paymentMethods, array $typeMap, string $type): array
    {
        $rows = [];
        $columnTotals = [];
        $grandTotal = 0.0;

        foreach ($paymentMethods as $paymentMethod) {
            $columnTotals[$paymentMethod->name] = 0.0;
        }

        foreach ($bankAccounts as $bankAccount) {
            $rowValues = [];
            $rowTotal = 0.0;

            foreach ($paymentMethods as $paymentMethod) {
                $value = (float) ($typeMap[$bankAccount->id][$paymentMethod->id] ?? 0.0);
                $rowValues[$paymentMethod->name] = $value;
                $rowTotal += $value;
                $columnTotals[$paymentMethod->name] += $value;
            }

            $rows[] = [
                'name' => $bankAccount->name,
                'values' => $rowValues,
                'total' => $rowTotal,
            ];

            $grandTotal += $rowTotal;
        }

        if (empty($rows)) {
            $rows[] = [
                'name' => $type === 'income' ? 'Sem banco cadastrado' : 'Sem banco cadastrado',
                'values' => collect($paymentMethods)->mapWithKeys(function ($paymentMethod) {
                    return [$paymentMethod->name => 0.0];
                })->all(),
                'total' => 0.0,
            ];
        }

        return [
            'rows' => $rows,
            'columnTotals' => $columnTotals,
            'grandTotal' => $grandTotal,
        ];
    }

    private function parseMonth(?string $month): ?Carbon
    {
        if (! $month || ! preg_match('/^\d{4}-\d{2}$/', $month)) {
            return null;
        }

        try {
            return Carbon::createFromFormat('Y-m', $month)->startOfMonth();
        } catch (\Throwable $exception) {
            return null;
        }
    }
}
