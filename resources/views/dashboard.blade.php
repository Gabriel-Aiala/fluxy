<x-app-layout>
    @php
        $chartMax = max((float) $chart->max('income'), (float) $chart->max('expense'), 1);
    @endphp

    <div class="py-8">
        <div class="flux-shell space-y-6">
            <div class="grid grid-cols-1 gap-4 lg:grid-cols-4">
                <article class="flux-kpi flux-kpi-income">
                    <p class="text-sm text-[var(--flux-muted)]">Receitas</p>
                    <p class="mt-1 text-4xl font-bold text-[var(--flux-green)]">R$ {{ number_format($totals['income'], 2, ',', '.') }}</p>
                    <p class="mt-1 text-sm text-[var(--flux-muted)]">Total recebido</p>
                </article>

                <article class="flux-kpi flux-kpi-expense">
                    <p class="text-sm text-[var(--flux-muted)]">Despesas</p>
                    <p class="mt-1 text-4xl font-bold text-[var(--flux-orange)]">R$ {{ number_format($totals['expense'], 2, ',', '.') }}</p>
                    <p class="mt-1 text-sm text-[var(--flux-muted)]">Total gasto</p>
                </article>

                <article class="flux-kpi flux-kpi-income">
                    <p class="text-sm text-[var(--flux-muted)]">Saldo</p>
                    <p class="mt-1 text-4xl font-bold {{ $totals['balance'] >= 0 ? 'text-[var(--flux-green)]' : 'text-[var(--flux-orange)]' }}">
                        R$ {{ number_format($totals['balance'], 2, ',', '.') }}
                    </p>
                    <p class="mt-1 text-sm text-[var(--flux-muted)]">Receitas - Despesas</p>
                </article>

                <article class="flux-kpi flux-kpi-neutral">
                    <p class="text-sm text-[var(--flux-muted)]">Margem de Lucro</p>
                    <p class="mt-1 text-4xl font-bold text-[var(--flux-ink)]">{{ number_format($totals['margin'], 1, ',', '.') }}%</p>
                    <p class="mt-1 text-sm text-[var(--flux-muted)]">Lucro sobre receita</p>
                </article>
            </div>

            <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
                <article class="flux-card px-5 py-4">
                    <p class="text-sm text-[var(--flux-muted)]">A Receber</p>
                    <p class="mt-1 text-4xl font-bold text-[var(--flux-orange)]">R$ {{ number_format($totals['to_receive'], 2, ',', '.') }}</p>
                </article>
                <article class="flux-card px-5 py-4">
                    <p class="text-sm text-[var(--flux-muted)]">A Pagar</p>
                    <p class="mt-1 text-4xl font-bold text-[var(--flux-orange)]">R$ {{ number_format($totals['to_pay'], 2, ',', '.') }}</p>
                </article>
            </div>

            <section class="flux-card p-6">
                <div class="flex flex-wrap items-end justify-between gap-3">
                    <h2 class="font-display text-3xl font-bold text-[var(--flux-ink)]">Calendario</h2>
                    <p class="text-sm font-semibold uppercase tracking-widest text-[var(--flux-muted)]">{{ $calendarMonthLabel }}</p>
                </div>

                <div class="mt-6 grid grid-cols-7 gap-2">
                    @foreach (['Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sab', 'Dom'] as $weekDay)
                        <div class="px-2 py-1 text-center text-xs font-semibold uppercase tracking-wide text-[var(--flux-muted)]">
                            {{ $weekDay }}
                        </div>
                    @endforeach
                </div>

                <div class="mt-2 grid grid-cols-7 gap-2">
                    @for ($i = 0; $i < $calendarStartOffset; $i++)
                        <div class="min-h-24 rounded-xl border border-transparent"></div>
                    @endfor

                    @foreach ($calendarDays as $day)
                        @php
                            $dayClass = 'border-[var(--flux-border)] bg-white';
                            if ($day['has_receivable'] && $day['has_payable']) {
                                $dayClass = 'border-[#d9c7b6] bg-[#f6efe6]';
                            } elseif ($day['has_receivable']) {
                                $dayClass = 'border-[#b8d7a2] bg-[var(--flux-green-soft)]';
                            } elseif ($day['has_payable']) {
                                $dayClass = 'border-[#e5b18d] bg-[var(--flux-orange-soft)]';
                            }
                        @endphp

                        <div class="min-h-24 rounded-xl border p-2 {{ $dayClass }}">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-semibold {{ $day['is_today'] ? 'text-[var(--flux-dark)]' : 'text-[var(--flux-muted)]' }}">
                                    {{ str_pad((string) $day['day'], 2, '0', STR_PAD_LEFT) }}
                                </span>
                                @if ($day['is_today'])
                                    <span class="rounded-full bg-[var(--flux-dark)] px-1.5 py-0.5 text-[10px] font-semibold text-white">
                                        Hoje
                                    </span>
                                @endif
                            </div>

                            <div class="mt-2 space-y-1">
                                @if ($day['has_receivable'])
                                    <p class="text-[11px] font-semibold uppercase tracking-wide text-[var(--flux-green)]">A receber</p>
                                    <p class="text-xs font-mono text-[var(--flux-green)]">R$ {{ number_format($day['receivable_total'], 2, ',', '.') }}</p>
                                @endif

                                @if ($day['has_payable'])
                                    <p class="text-[11px] font-semibold uppercase tracking-wide text-[var(--flux-orange)]">A pagar</p>
                                    <p class="text-xs font-mono text-[var(--flux-orange)]">R$ {{ number_format($day['payable_total'], 2, ',', '.') }}</p>
                                @endif

                                @if (! $day['has_receivable'] && ! $day['has_payable'])
                                    <p class="pt-3 text-[11px] text-[var(--flux-muted)]">Sem movimentacao</p>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>

            <section class="flux-card p-6">
                <h2 class="font-display text-3xl font-bold text-[var(--flux-ink)]">Fluxo de Caixa</h2>

                <div class="mt-6 grid grid-cols-1 gap-6 lg:grid-cols-2">
                    @foreach ($chart as $month)
                        <div class="rounded-2xl border border-[var(--flux-border)] bg-[#fafaf7] p-4">
                            <div class="mb-3 text-center text-sm font-semibold uppercase tracking-widest text-[var(--flux-muted)]">
                                {{ $month['label'] }}
                            </div>

                            <div class="flex h-56 items-end gap-4">
                                <div class="flex-1">
                                    <div class="w-full rounded-t-xl bg-[var(--flux-green)]"
                                        style="height: {{ max(8, (($month['income'] / $chartMax) * 210)) }}px;"></div>
                                    <p class="mt-2 text-center text-sm font-semibold text-[var(--flux-green)]">Receitas</p>
                                </div>
                                <div class="flex-1">
                                    <div class="w-full rounded-t-xl bg-[var(--flux-orange)]"
                                        style="height: {{ max(8, (($month['expense'] / $chartMax) * 210)) }}px;"></div>
                                    <p class="mt-2 text-center text-sm font-semibold text-[var(--flux-orange)]">Despesas</p>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>

            <section class="flux-card overflow-hidden p-0">
                <details>
                    <summary class="cursor-pointer px-6 py-4 text-sm font-semibold uppercase tracking-widest text-[var(--flux-muted)]">
                        Totais por categoria (clique para expandir)
                    </summary>

                    <div class="border-t border-[var(--flux-border)] p-6">
                        <div class="grid grid-cols-1 gap-5 lg:grid-cols-2">
                            <article class="rounded-2xl border border-[var(--flux-border)] bg-white/70 p-4">
                                <h3 class="text-sm font-semibold uppercase tracking-widest text-[var(--flux-green)]">Entradas por categoria</h3>
                                <div class="mt-3 overflow-x-auto">
                                    <table class="min-w-full divide-y divide-[var(--flux-border)] text-sm">
                                        <thead>
                                            <tr class="text-left text-xs font-semibold uppercase tracking-wide text-[var(--flux-muted)]">
                                                <th class="py-2">Categoria</th>
                                                <th class="py-2 text-right">Total</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-[var(--flux-border)]">
                                            @forelse ($incomeCategoryTotals as $item)
                                                <tr>
                                                    <td class="py-2 text-[var(--flux-ink)]">{{ $item['category'] }}</td>
                                                    <td class="py-2 text-right font-mono text-[var(--flux-green)]">
                                                        R$ {{ number_format($item['total'], 2, ',', '.') }}
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="2" class="py-3 text-center text-sm text-[var(--flux-muted)]">
                                                        Sem entradas por categoria.
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </article>

                            <article class="rounded-2xl border border-[var(--flux-border)] bg-white/70 p-4">
                                <h3 class="text-sm font-semibold uppercase tracking-widest text-[var(--flux-orange)]">Saidas por categoria</h3>
                                <div class="mt-3 overflow-x-auto">
                                    <table class="min-w-full divide-y divide-[var(--flux-border)] text-sm">
                                        <thead>
                                            <tr class="text-left text-xs font-semibold uppercase tracking-wide text-[var(--flux-muted)]">
                                                <th class="py-2">Categoria</th>
                                                <th class="py-2 text-right">Total</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-[var(--flux-border)]">
                                            @forelse ($expenseCategoryTotals as $item)
                                                <tr>
                                                    <td class="py-2 text-[var(--flux-ink)]">{{ $item['category'] }}</td>
                                                    <td class="py-2 text-right font-mono text-[var(--flux-orange)]">
                                                        R$ {{ number_format($item['total'], 2, ',', '.') }}
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="2" class="py-3 text-center text-sm text-[var(--flux-muted)]">
                                                        Sem saidas por categoria.
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </article>
                        </div>
                    </div>
                </details>
            </section>
        </div>
    </div>
</x-app-layout>
