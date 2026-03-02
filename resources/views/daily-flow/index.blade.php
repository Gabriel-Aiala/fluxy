<x-app-layout>
    <div class="py-8">
        <div class="flux-shell space-y-5">
            <div>
                <h1 class="font-display text-4xl font-bold text-[var(--flux-ink)]">Fluxo Diario</h1>
                <p class="mt-1 text-lg text-[var(--flux-muted)]">Controle dia a dia de recebimentos, pagamentos e saldo</p>
            </div>

            <div class="flex flex-wrap gap-2">
                @foreach ($monthTabs as $month)
                    @php($isActive = $month['number'] === $selectedMonth->month)
                    <a href="{{ route('daily-flow.index', ['month' => $selectedMonth->copy()->month($month['number'])->format('Y-m')]) }}"
                        class="{{ $isActive ? 'flux-pill-nav flux-pill-nav-active' : 'flux-pill-nav' }}">
                        {{ $month['short'] }}
                    </a>
                @endforeach
            </div>

            <div class="grid grid-cols-1 gap-4 lg:grid-cols-4">
                <article class="flux-kpi flux-card-soft">
                    <p class="text-sm text-[var(--flux-muted)]">Saldo Anterior</p>
                    <p class="mt-1 text-4xl font-bold text-[var(--flux-ink)]">R$ {{ number_format($summary['previous_balance'], 2, ',', '.') }}</p>
                </article>
                <article class="flux-kpi flux-kpi-income">
                    <p class="text-sm text-[var(--flux-muted)]">Total Recebido</p>
                    <p class="mt-1 text-4xl font-bold text-[var(--flux-green)]">R$ {{ number_format($summary['received'], 2, ',', '.') }}</p>
                </article>
                <article class="flux-kpi flux-kpi-expense">
                    <p class="text-sm text-[var(--flux-muted)]">Total Pago</p>
                    <p class="mt-1 text-4xl font-bold text-[var(--flux-orange)]">R$ {{ number_format($summary['paid'], 2, ',', '.') }}</p>
                </article>
                <article class="flux-kpi flux-kpi-neutral">
                    <p class="text-sm text-[var(--flux-muted)]">Saldo Final</p>
                    <p class="mt-1 text-4xl font-bold {{ $summary['final_balance'] >= 0 ? 'text-[var(--flux-green)]' : 'text-[var(--flux-orange)]' }}">
                        R$ {{ number_format($summary['final_balance'], 2, ',', '.') }}
                    </p>
                </article>
            </div>

            <section class="flux-table-wrap overflow-visible">
                <div class="overflow-x-auto lg:overflow-visible">
                    <table class="flux-table min-w-[1300px]">
                        <thead>
                            <tr>
                                <th class="sticky top-[5.5rem] z-30 bg-[var(--flux-dark)] text-white sm:top-[7.5rem]">Data</th>
                                <th class="sticky top-[5.5rem] z-30 bg-[var(--flux-green-soft)] text-[var(--flux-green)] sm:top-[7.5rem]">Recebido</th>
                                <th class="sticky top-[5.5rem] z-30 bg-[var(--flux-orange-soft)] text-[var(--flux-orange)] sm:top-[7.5rem]">Pago</th>
                                <th class="sticky top-[5.5rem] z-30 bg-[#efdccf] text-[var(--flux-orange)] sm:top-[7.5rem]">Aplicacao</th>
                                <th class="sticky top-[5.5rem] z-30 bg-white sm:top-[7.5rem]">Saldo Atual</th>
                                <th class="sticky top-[5.5rem] z-30 bg-[var(--flux-green-soft)] text-[var(--flux-green)] sm:top-[7.5rem]">A Receber</th>
                                <th class="sticky top-[5.5rem] z-30 bg-[var(--flux-orange-soft)] text-[var(--flux-orange)] sm:top-[7.5rem]">A Pagar</th>
                                <th class="sticky top-[5.5rem] z-30 bg-[var(--flux-neutral-soft)] sm:top-[7.5rem]">Saldo Prev.</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="bg-[#f3f4ef]">
                                <td class="font-semibold">Anterior</td>
                                <td class="text-center">-</td>
                                <td class="text-center">-</td>
                                <td class="font-semibold">Saldo: R$ {{ number_format($summary['previous_balance'], 2, ',', '.') }}</td>
                                <td class="font-semibold text-[var(--flux-ink)]">R$ {{ number_format($summary['previous_balance'], 2, ',', '.') }}</td>
                                <td class="text-center">-</td>
                                <td class="text-center">-</td>
                                <td class="font-semibold text-[var(--flux-ink)]">R$ {{ number_format($summary['previous_balance'], 2, ',', '.') }}</td>
                            </tr>
                            @foreach ($rows as $row)
                                <tr>
                                    <td class="font-semibold">{{ $row['day'] }}</td>
                                    <td class="font-mono text-[var(--flux-green)]">
                                        {{ $row['received'] > 0 ? 'R$ ' . number_format($row['received'], 2, ',', '.') : '-' }}
                                    </td>
                                    <td class="font-mono text-[var(--flux-orange)]">
                                        {{ $row['paid'] > 0 ? 'R$ ' . number_format($row['paid'], 2, ',', '.') : '-' }}
                                    </td>
                                    <td class="font-mono {{ $row['application'] >= 0 ? 'text-[var(--flux-green)]' : 'text-[var(--flux-orange)]' }}">
                                        R$ {{ number_format($row['application'], 2, ',', '.') }}
                                    </td>
                                    <td class="font-mono font-semibold {{ $row['current_balance'] >= 0 ? 'text-[var(--flux-green)]' : 'text-[var(--flux-orange)]' }}">
                                        R$ {{ number_format($row['current_balance'], 2, ',', '.') }}
                                    </td>
                                    <td class="font-mono text-[var(--flux-green)]">
                                        {{ $row['receivable'] > 0 ? 'R$ ' . number_format($row['receivable'], 2, ',', '.') : '-' }}
                                    </td>
                                    <td class="font-mono text-[var(--flux-orange)]">
                                        {{ $row['payable'] > 0 ? 'R$ ' . number_format($row['payable'], 2, ',', '.') : '-' }}
                                    </td>
                                    <td class="font-mono font-semibold {{ $row['predicted_balance'] >= 0 ? 'text-[var(--flux-green)]' : 'text-[var(--flux-orange)]' }}">
                                        R$ {{ number_format($row['predicted_balance'], 2, ',', '.') }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </div>
</x-app-layout>
