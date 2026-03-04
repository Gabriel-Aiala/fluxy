<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Transaction;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;

class ControlController extends Controller
{
    public function index()
    {
        $now = now();
        $year = (int) $now->year;

        $fullMonthNames = [
            1 => 'JANEIRO',
            2 => 'FEVEREIRO',
            3 => 'MARCO',
            4 => 'ABRIL',
            5 => 'MAIO',
            6 => 'JUNHO',
            7 => 'JULHO',
            8 => 'AGOSTO',
            9 => 'SETEMBRO',
            10 => 'OUTUBRO',
            11 => 'NOVEMBRO',
            12 => 'DEZEMBRO',
        ];

        $shortMonthNames = [
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

        $monthTabs = collect(range(1, 12))->map(function (int $month) use ($shortMonthNames) {
            return [
                'number' => $month,
                'short' => $shortMonthNames[$month],
            ];
        })->values()->all();

        $cards = collect([-2, -1, 0, 1])->map(function (int $offset) use ($now, $fullMonthNames) {
            $month = $now->copy()->startOfMonth()->addMonthsNoOverflow($offset);

            return [
                'label' => $fullMonthNames[(int) $month->format('n')],
                'month' => (int) $month->format('n'),
                'year' => (int) $month->format('Y'),
                'is_current' => $month->isSameMonth($now),
                'received' => 0.0,
                'to_receive' => 0.0,
                'paid' => 0.0,
                'to_pay' => 0.0,
                'result' => 0.0,
            ];
        })->values();

        $rows = [];

        if (Schema::hasTable('transaction') && Schema::hasTable('category')) {
            $cards = $cards->map(function (array $card) {
                $received = (float) Transaction::query()
                    ->where('type', 'income')
                    ->where('payment_status', 'paid')
                    ->whereYear('payment_date', $card['year'])
                    ->whereMonth('payment_date', $card['month'])
                    ->sum('amount');

                $toReceive = (float) Transaction::query()
                    ->where('type', 'income')
                    ->where('payment_status', 'payable')
                    ->whereYear('expected_payment_date', $card['year'])
                    ->whereMonth('expected_payment_date', $card['month'])
                    ->sum('amount');

                $paid = (float) Transaction::query()
                    ->where('type', 'expense')
                    ->where('payment_status', 'paid')
                    ->whereYear('payment_date', $card['year'])
                    ->whereMonth('payment_date', $card['month'])
                    ->sum('amount');

                $toPay = (float) Transaction::query()
                    ->where('type', 'expense')
                    ->where('payment_status', 'payable')
                    ->whereYear('expected_payment_date', $card['year'])
                    ->whereMonth('expected_payment_date', $card['month'])
                    ->sum('amount');

                $card['received'] = $received;
                $card['to_receive'] = $toReceive;
                $card['paid'] = $paid;
                $card['to_pay'] = $toPay;
                $card['result'] = $received - $paid;

                return $card;
            })->values();

            $incomeByMonth = Transaction::query()
                ->selectRaw('MONTH(payment_date) as month_number, SUM(amount) as total')
                ->whereYear('payment_date', $year)
                ->where('type', 'income')
                ->groupByRaw('MONTH(payment_date)')
                ->pluck('total', 'month_number')
                ->map(fn ($value) => (float) $value)
                ->all();

            $fixedExpenseByMonth = Transaction::query()
                ->selectRaw('MONTH(transaction.payment_date) as month_number, SUM(transaction.amount) as total')
                ->join('category', 'category.id', '=', 'transaction.category_id')
                ->whereYear('transaction.payment_date', $year)
                ->where('transaction.type', 'expense')
                ->where('category.cost_type', 'fixed')
                ->groupByRaw('MONTH(transaction.payment_date)')
                ->pluck('total', 'month_number')
                ->map(fn ($value) => (float) $value)
                ->all();

            $variableExpenseByMonth = Transaction::query()
                ->selectRaw('MONTH(transaction.payment_date) as month_number, SUM(transaction.amount) as total')
                ->join('category', 'category.id', '=', 'transaction.category_id')
                ->whereYear('transaction.payment_date', $year)
                ->where('transaction.type', 'expense')
                ->where('category.cost_type', 'variable')
                ->groupByRaw('MONTH(transaction.payment_date)')
                ->pluck('total', 'month_number')
                ->map(fn ($value) => (float) $value)
                ->all();

            $expenseByCategoryMonth = Transaction::query()
                ->selectRaw('category_id, MONTH(payment_date) as month_number, SUM(amount) as total')
                ->whereYear('payment_date', $year)
                ->where('type', 'expense')
                ->groupBy('category_id')
                ->groupByRaw('MONTH(payment_date)')
                ->get();

            $categoryMonthTotals = [];
            foreach ($expenseByCategoryMonth as $item) {
                $categoryMonthTotals[(int) $item->category_id][(int) $item->month_number] = (float) $item->total;
            }

            $fixedCategories = Category::query()
                ->where('type', 'expense')
                ->where('cost_type', 'fixed')
                ->orderBy('name')
                ->get(['id', 'name']);

            $variableCategories = Category::query()
                ->where('type', 'expense')
                ->where('cost_type', 'variable')
                ->orderBy('name')
                ->get(['id', 'name']);

            $rows[] = [
                'type' => 'section',
                'variant' => 'income',
                'label' => 'Entradas',
                'months' => $this->toMonthMap($monthTabs, $incomeByMonth),
            ];

            $rows[] = [
                'type' => 'section',
                'variant' => 'fixed-expense',
                'label' => 'Despesas Fixas',
                'months' => $this->toMonthMap($monthTabs, $fixedExpenseByMonth),
            ];

            foreach ($fixedCategories as $category) {
                $rows[] = [
                    'type' => 'category',
                    'label' => $category->name,
                    'months' => $this->toMonthMap($monthTabs, $categoryMonthTotals[$category->id] ?? []),
                ];
            }

            $rows[] = [
                'type' => 'section',
                'variant' => 'common-expense',
                'label' => 'Custos Variaveis',
                'months' => $this->toMonthMap($monthTabs, $variableExpenseByMonth),
            ];

            foreach ($variableCategories as $category) {
                $rows[] = [
                    'type' => 'category',
                    'label' => $category->name,
                    'months' => $this->toMonthMap($monthTabs, $categoryMonthTotals[$category->id] ?? []),
                ];
            }
        }

        return view('control.index', [
            'cards' => $cards->all(),
            'monthTabs' => $monthTabs,
            'rows' => $rows,
        ]);
    }

    private function toMonthMap(array $monthTabs, array $totalsByMonth): array
    {
        $months = [];

        foreach ($monthTabs as $month) {
            $monthNumber = (int) $month['number'];
            $months[$monthNumber] = [
                'real' => (float) ($totalsByMonth[$monthNumber] ?? 0),
            ];
        }

        return $months;
    }
}
