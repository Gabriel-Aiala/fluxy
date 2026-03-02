<x-app-layout>
    @php
        $prevMonth = $selectedMonth->copy()->subMonth()->format('Y-m');
        $nextMonth = $selectedMonth->copy()->addMonth()->format('Y-m');
        $monthNames = [
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
    @endphp

    <div class="py-8">
        <div class="flux-shell space-y-5">
            <div class="flex flex-wrap items-center justify-between gap-3 flux-card px-4 py-3">
                <div class="flex items-center gap-2">
                    <span class="text-sm font-semibold uppercase tracking-wide text-[var(--flux-muted)]">Mes:</span>
                    <a href="{{ route('bank.index', ['month' => $prevMonth]) }}"
                        class="flux-secondary-btn px-3 py-1.5">
                        &lt;
                    </a>
                    <form method="GET" action="{{ route('bank.index') }}">
                        <input type="month" name="month" value="{{ $selectedMonth->format('Y-m') }}"
                            class="rounded-xl border-[var(--flux-border)] bg-white px-3 py-1.5 text-sm shadow-sm focus:border-[#17736a] focus:ring-[#17736a]"
                            onchange="this.form.submit()">
                    </form>
                    <a href="{{ route('bank.index', ['month' => $nextMonth]) }}"
                        class="flux-secondary-btn px-3 py-1.5">
                        &gt;
                    </a>
                </div>

                <p class="text-sm font-semibold text-[var(--flux-muted)]">
                    {{ $monthNames[$selectedMonth->month] ?? $selectedMonth->format('m') }} de {{ $selectedMonth->year }}
                </p>
            </div>

            <div class="grid grid-cols-1 gap-4 lg:grid-cols-3">
                <article class="flux-kpi flux-kpi-income">
                    <p class="text-sm text-[var(--flux-muted)]">Total Entradas</p>
                    <p class="mt-1 text-4xl font-bold text-[var(--flux-green)]">R$ {{ number_format($totals['income'], 2, ',', '.') }}</p>
                </article>
                <article class="flux-kpi flux-kpi-expense">
                    <p class="text-sm text-[var(--flux-muted)]">Total Saidas</p>
                    <p class="mt-1 text-4xl font-bold text-[var(--flux-orange)]">R$ {{ number_format($totals['expense'], 2, ',', '.') }}</p>
                </article>
                <article class="flux-kpi flux-kpi-neutral">
                    <p class="text-sm text-[var(--flux-muted)]">Saldo</p>
                    <p class="mt-1 text-4xl font-bold {{ $totals['balance'] >= 0 ? 'text-[var(--flux-green)]' : 'text-[var(--flux-orange)]' }}">
                        R$ {{ number_format($totals['balance'], 2, ',', '.') }}
                    </p>
                </article>
            </div>

            <section class="flux-table-wrap">
                <div class="bg-[var(--flux-green)] px-4 py-3 text-center text-xl font-bold uppercase tracking-wide text-white">
                    Entradas
                </div>
                <div class="overflow-x-auto">
                    <table class="flux-table min-w-[1150px]">
                        <thead>
                            <tr>
                                <th>Banco</th>
                                @foreach ($paymentMethods as $paymentMethod)
                                    <th>{{ $paymentMethod->name }}</th>
                                @endforeach
                                <th class="text-right">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($incomeTable['rows'] as $row)
                                <tr>
                                    <td class="font-semibold">{{ $row['name'] }}</td>
                                    @foreach ($paymentMethods as $paymentMethod)
                                        <td class="font-mono text-[var(--flux-muted)]">
                                            {{ $row['values'][$paymentMethod->name] > 0 ? 'R$ ' . number_format($row['values'][$paymentMethod->name], 2, ',', '.') : 'R$ -' }}
                                        </td>
                                    @endforeach
                                    <td class="text-right font-mono font-semibold text-[var(--flux-green)]">
                                        R$ {{ number_format($row['total'], 2, ',', '.') }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="border-t border-[var(--flux-border)] bg-[#f2f5ec] font-semibold">
                                <td>Total</td>
                                @foreach ($paymentMethods as $paymentMethod)
                                    <td class="font-mono text-[var(--flux-green)]">
                                        R$ {{ number_format($incomeTable['columnTotals'][$paymentMethod->name] ?? 0, 2, ',', '.') }}
                                    </td>
                                @endforeach
                                <td class="text-right font-mono text-[var(--flux-green)]">
                                    R$ {{ number_format($incomeTable['grandTotal'], 2, ',', '.') }}
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </section>

            <section class="flux-table-wrap">
                <div class="bg-[var(--flux-orange)] px-4 py-3 text-center text-xl font-bold uppercase tracking-wide text-white">
                    Saidas
                </div>
                <div class="overflow-x-auto">
                    <table class="flux-table min-w-[1150px]">
                        <thead>
                            <tr>
                                <th>Banco</th>
                                @foreach ($paymentMethods as $paymentMethod)
                                    <th>{{ $paymentMethod->name }}</th>
                                @endforeach
                                <th class="text-right">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($expenseTable['rows'] as $row)
                                <tr>
                                    <td class="font-semibold">{{ $row['name'] }}</td>
                                    @foreach ($paymentMethods as $paymentMethod)
                                        <td class="font-mono text-[var(--flux-muted)]">
                                            {{ $row['values'][$paymentMethod->name] > 0 ? 'R$ ' . number_format($row['values'][$paymentMethod->name], 2, ',', '.') : 'R$ -' }}
                                        </td>
                                    @endforeach
                                    <td class="text-right font-mono font-semibold text-[var(--flux-orange)]">
                                        R$ {{ number_format($row['total'], 2, ',', '.') }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="border-t border-[var(--flux-border)] bg-[#f8efe8] font-semibold">
                                <td>Total</td>
                                @foreach ($paymentMethods as $paymentMethod)
                                    <td class="font-mono text-[var(--flux-orange)]">
                                        R$ {{ number_format($expenseTable['columnTotals'][$paymentMethod->name] ?? 0, 2, ',', '.') }}
                                    </td>
                                @endforeach
                                <td class="text-right font-mono text-[var(--flux-orange)]">
                                    R$ {{ number_format($expenseTable['grandTotal'], 2, ',', '.') }}
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </section>
        </div>
    </div>
</x-app-layout>
