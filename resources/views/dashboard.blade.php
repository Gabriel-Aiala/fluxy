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

            <div x-data="dashboardCalendar(@js($calendarDayDetails))">
                <section class="flux-card p-6">
                    <div class="flex flex-wrap items-end justify-between gap-3">
                        <div>
                            <h2 class="font-display text-3xl font-bold text-[var(--flux-ink)]">Calendario</h2>
                            <p class="mt-1 text-sm font-semibold uppercase tracking-widest text-[var(--flux-muted)]">{{ $calendarMonthLabel }}</p>
                        </div>

                        <div class="flex items-center gap-2">
                            <a href="{{ route('dashboard', ['month' => $calendarPrevMonth]) }}" class="flux-secondary-btn px-3 py-1.5">
                                &lt;
                            </a>
                            <form method="GET" action="{{ route('dashboard') }}">
                                <input type="month" name="month" value="{{ $calendarSelectedMonth }}"
                                    class="rounded-xl border-[var(--flux-border)] bg-white px-3 py-1.5 text-sm shadow-sm focus:border-[#17736a] focus:ring-[#17736a]"
                                    onchange="this.form.submit()">
                            </form>
                            <a href="{{ route('dashboard', ['month' => $calendarNextMonth]) }}" class="flux-secondary-btn px-3 py-1.5">
                                &gt;
                            </a>
                        </div>
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

                            @if ($day['has_transactions'])
                                <button
                                    type="button"
                                    class="min-h-24 w-full rounded-xl border p-2 text-left transition hover:shadow-sm focus:outline-none focus:ring-2 focus:ring-[#17736a] {{ $dayClass }}"
                                    data-calendar-day="{{ $day['iso_date'] }}"
                                    data-has-transactions="1"
                                    x-on:click="openDayDetails('{{ $day['iso_date'] }}')">
                            @else
                                <div
                                    class="min-h-24 rounded-xl border p-2 {{ $dayClass }}"
                                    data-calendar-day="{{ $day['iso_date'] }}"
                                    data-has-transactions="0">
                            @endif
                                <div class="flex items-center justify-between gap-1">
                                    <span class="text-xs font-semibold {{ $day['is_today'] ? 'text-[var(--flux-dark)]' : 'text-[var(--flux-muted)]' }}">
                                        {{ str_pad((string) $day['day'], 2, '0', STR_PAD_LEFT) }}
                                    </span>
                                    @if ($day['is_today'])
                                        <span class="rounded-full bg-[var(--flux-dark)] px-1.5 py-0.5 text-[10px] font-semibold text-white">
                                            Hoje
                                        </span>
                                    @endif
                                </div>

                                <div class="mt-1 flex flex-wrap gap-1">
                                    @if ($day['has_done'])
                                        <span data-day-badge="done" class="rounded-full bg-[#e8f0ff] px-1.5 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-[#1e40af]">
                                            Feito
                                        </span>
                                    @endif
                                    @if ($day['has_planned'])
                                        <span data-day-badge="planned" class="rounded-full bg-[#fff3e6] px-1.5 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-[var(--flux-orange)]">
                                            Planejado
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

                                    @if (! $day['has_transactions'])
                                        <p class="pt-3 text-[11px] text-[var(--flux-muted)]">Sem movimentacao</p>
                                    @endif
                                </div>
                            @if ($day['has_transactions'])
                                </button>
                            @else
                                </div>
                            @endif
                        @endforeach
                    </div>
                </section>

                <x-modal name="dashboard-day-transactions" maxWidth="2xl">
                    <div class="space-y-4 p-6">
                        <div class="flex flex-wrap items-start justify-between gap-3">
                            <div>
                                <h3 class="font-display text-2xl font-bold text-[var(--flux-ink)]">Transacoes do dia</h3>
                                <p class="mt-1 text-sm font-semibold uppercase tracking-wide text-[var(--flux-muted)]" x-text="selectedDayKey ? formatDate(selectedDayKey) : '-'"></p>
                            </div>
                            <button type="button" x-on:click="$dispatch('close-modal', 'dashboard-day-transactions')" class="flux-secondary-btn">
                                Fechar
                            </button>
                        </div>

                        <div class="grid grid-cols-1 gap-3 md:grid-cols-4">
                            <article class="rounded-xl border border-[#d9e9d4] bg-[var(--flux-green-soft)] p-3">
                                <p class="text-xs font-semibold uppercase tracking-wide text-[var(--flux-green)]">Feito Receita</p>
                                <p class="mt-1 text-lg font-bold text-[var(--flux-green)]" x-text="formatMoney(selectedDayPayload ? selectedDayPayload.done_income_total : 0)"></p>
                            </article>
                            <article class="rounded-xl border border-[#f2cbb0] bg-[var(--flux-orange-soft)] p-3">
                                <p class="text-xs font-semibold uppercase tracking-wide text-[var(--flux-orange)]">Feito Despesa</p>
                                <p class="mt-1 text-lg font-bold text-[var(--flux-orange)]" x-text="formatMoney(selectedDayPayload ? selectedDayPayload.done_expense_total : 0)"></p>
                            </article>
                            <article class="rounded-xl border border-[#d9e9d4] bg-[var(--flux-green-soft)] p-3">
                                <p class="text-xs font-semibold uppercase tracking-wide text-[var(--flux-green)]">Planejado Receita</p>
                                <p class="mt-1 text-lg font-bold text-[var(--flux-green)]" x-text="formatMoney(selectedDayPayload ? selectedDayPayload.planned_income_total : 0)"></p>
                            </article>
                            <article class="rounded-xl border border-[#f2cbb0] bg-[var(--flux-orange-soft)] p-3">
                                <p class="text-xs font-semibold uppercase tracking-wide text-[var(--flux-orange)]">Planejado Despesa</p>
                                <p class="mt-1 text-lg font-bold text-[var(--flux-orange)]" x-text="formatMoney(selectedDayPayload ? selectedDayPayload.planned_expense_total : 0)"></p>
                            </article>
                        </div>

                        <div class="max-h-[26rem] overflow-y-auto rounded-xl border border-[var(--flux-border)]">
                            <table class="min-w-full divide-y divide-[var(--flux-border)] text-sm">
                                <thead class="bg-[#f8f9f5]">
                                    <tr class="text-left text-xs font-semibold uppercase tracking-wide text-[var(--flux-muted)]">
                                        <th class="px-3 py-2">Status</th>
                                        <th class="px-3 py-2">Tipo</th>
                                        <th class="px-3 py-2">Categoria</th>
                                        <th class="px-3 py-2">Contraparte</th>
                                        <th class="px-3 py-2">Conta</th>
                                        <th class="px-3 py-2">Metodo</th>
                                        <th class="px-3 py-2">Parcela</th>
                                        <th class="px-3 py-2 text-right">Valor</th>
                                        <th class="px-3 py-2 text-right">Acao</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-[var(--flux-border)] text-[var(--flux-ink)]">
                                    <template x-if="dayTransactions().length === 0">
                                        <tr>
                                            <td colspan="9" class="px-3 py-5 text-center text-sm text-[var(--flux-muted)]">
                                                Sem transacoes para este dia.
                                            </td>
                                        </tr>
                                    </template>
                                    <template x-for="transaction in dayTransactions()" :key="transaction.id">
                                        <tr>
                                            <td class="px-3 py-2">
                                                <span class="rounded-full px-2 py-1 text-xs font-semibold"
                                                    :class="statusBadgeClass(transaction.payment_status)"
                                                    x-text="transaction.status_label_ptbr">
                                                </span>
                                            </td>
                                            <td class="px-3 py-2">
                                                <span class="rounded-full px-2 py-1 text-xs font-semibold"
                                                    :class="typeBadgeClass(transaction.type)"
                                                    x-text="transaction.type_label_ptbr">
                                                </span>
                                            </td>
                                            <td class="px-3 py-2" x-text="transaction.category_name"></td>
                                            <td class="px-3 py-2" x-text="transaction.counterparty_name"></td>
                                            <td class="px-3 py-2" x-text="transaction.bank_account_name"></td>
                                            <td class="px-3 py-2" x-text="transaction.payment_method_name"></td>
                                            <td class="px-3 py-2" x-text="transaction.installment_number"></td>
                                            <td class="px-3 py-2 text-right font-mono" x-text="formatMoney(transaction.amount)"></td>
                                            <td class="px-3 py-2 text-right">
                                                <a class="text-xs font-semibold text-[var(--flux-dark)] transition hover:underline" :href="transaction.edit_url">
                                                    Editar
                                                </a>
                                            </td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>

                        <div class="flex justify-end">
                            <button type="button" x-on:click="$dispatch('close-modal', 'dashboard-day-transactions')" class="flux-secondary-btn">
                                Fechar
                            </button>
                        </div>
                    </div>
                </x-modal>
            </div>

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

    <script>
        window.dashboardCalendar = function (dayDetailsMap) {
            return {
                dayDetailsMap: dayDetailsMap || {},
                selectedDayKey: null,
                selectedDayPayload: null,

                openDayDetails(isoDate) {
                    if (!isoDate || !this.dayDetailsMap[isoDate]) {
                        return;
                    }

                    this.selectedDayKey = isoDate;
                    this.selectedDayPayload = this.dayDetailsMap[isoDate];
                    window.dispatchEvent(new CustomEvent('open-modal', { detail: 'dashboard-day-transactions' }));
                },

                dayTransactions() {
                    if (!this.selectedDayPayload || !Array.isArray(this.selectedDayPayload.transactions)) {
                        return [];
                    }

                    return this.selectedDayPayload.transactions;
                },

                formatMoney(value) {
                    const numericValue = Number(value || 0);
                    return new Intl.NumberFormat('pt-BR', {
                        style: 'currency',
                        currency: 'BRL',
                    }).format(numericValue);
                },

                formatDate(isoDate) {
                    if (!isoDate || typeof isoDate !== 'string') {
                        return '-';
                    }

                    const parts = isoDate.split('-');
                    if (parts.length !== 3) {
                        return isoDate;
                    }

                    return `${parts[2]}/${parts[1]}/${parts[0]}`;
                },

                statusBadgeClass(paymentStatus) {
                    return paymentStatus === 'paid'
                        ? 'bg-[#e8f0ff] text-[#1e40af]'
                        : 'bg-[var(--flux-orange-soft)] text-[var(--flux-orange)]';
                },

                typeBadgeClass(type) {
                    return type === 'income'
                        ? 'bg-[var(--flux-green-soft)] text-[var(--flux-green)]'
                        : 'bg-[var(--flux-orange-soft)] text-[var(--flux-orange)]';
                },
            };
        };
    </script>
</x-app-layout>
