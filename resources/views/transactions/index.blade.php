<x-app-layout>
    @php
        $type = $filters['type'] ?? null;
        $status = $filters['status'] ?? null;
        $dateFrom = $filters['date_from'] ?? null;
        $dateTo = $filters['date_to'] ?? null;

        $newButtonHref = route('transactions.create', array_filter(['type' => $type]));
        $clearHref = route('transactions.index', array_filter(['type' => $type]));

        $primaryKpiClass = $type === 'expense' ? 'flux-kpi-expense' : 'flux-kpi-income';
        $paidStatusLabel = $context['status_paid_label'];
        $pendingStatusLabel = 'Pendente';

        $dateColumnLabel = 'Data ' . $context['date_label'];
        $dateRangeLabel = $context['date_label'];
    @endphp

    <div class="py-8">
        <div class="flux-shell space-y-6">
            @if (session('success'))
                <section class="flux-card p-4 text-sm font-semibold text-[var(--flux-green)]">
                    {{ session('success') }}
                </section>
            @endif

            <section class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
                <div>
                    <h1 class="font-display text-6xl font-bold text-[var(--flux-ink)]">{{ $context['page_title'] }}</h1>
                    <p class="mt-1 text-4xl text-[var(--flux-muted)]">{{ $context['page_description'] }}</p>
                </div>

                <a href="{{ $newButtonHref }}" class="flux-primary-btn mt-2 md:mt-1">
                    <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path d="M10 4a1 1 0 011 1v4h4a1 1 0 110 2h-4v4a1 1 0 11-2 0v-4H5a1 1 0 110-2h4V5a1 1 0 011-1z" />
                    </svg>
                    {{ $context['new_button_label'] }}
                </a>
            </section>

            <section class="flux-card p-4">
                <form method="GET" action="{{ route('transactions.index') }}" class="grid grid-cols-1 gap-3 lg:grid-cols-12 lg:items-end">
                    @if ($type)
                        <input type="hidden" name="type" value="{{ $type }}">
                    @endif

                    <div class="lg:col-span-3">
                        <label for="status" class="mb-1 block text-sm font-semibold text-[var(--flux-muted)]">Status</label>
                        <select id="status" name="status"
                            class="block w-full rounded-xl border border-[var(--flux-border)] bg-white px-3 py-2 text-sm text-[var(--flux-ink)] focus:border-[#9eb4a4] focus:ring-[#9eb4a4]">
                            <option value="">Todos</option>
                            <option value="paid" @selected($status === 'paid')>{{ $paidStatusLabel }}</option>
                            <option value="payable" @selected($status === 'payable')>{{ $pendingStatusLabel }}</option>
                        </select>
                    </div>

                    <div class="lg:col-span-3">
                        <label for="date_from" class="mb-1 block text-sm font-semibold text-[var(--flux-muted)]">{{ $dateRangeLabel }} de</label>
                        <input id="date_from" name="date_from" type="date" value="{{ $dateFrom }}"
                            class="block w-full rounded-xl border border-[var(--flux-border)] bg-white px-3 py-2 text-sm text-[var(--flux-ink)] focus:border-[#9eb4a4] focus:ring-[#9eb4a4]">
                    </div>

                    <div class="lg:col-span-3">
                        <label for="date_to" class="mb-1 block text-sm font-semibold text-[var(--flux-muted)]">{{ $dateRangeLabel }} ate</label>
                        <input id="date_to" name="date_to" type="date" value="{{ $dateTo }}"
                            class="block w-full rounded-xl border border-[var(--flux-border)] bg-white px-3 py-2 text-sm text-[var(--flux-ink)] focus:border-[#9eb4a4] focus:ring-[#9eb4a4]">
                    </div>

                    <div class="lg:col-span-3 flex items-end gap-2">
                        <button type="submit" class="flux-primary-btn">
                            Filtrar
                        </button>
                        <a href="{{ $clearHref }}" class="flux-secondary-btn">Limpar</a>
                    </div>
                </form>
            </section>

            <section class="grid grid-cols-1 gap-4 lg:grid-cols-3">
                <article class="flux-kpi {{ $primaryKpiClass }}">
                    <p class="text-sm text-[var(--flux-muted)]">{{ $context['total_card_label'] }}</p>
                    <p class="mt-1 text-4xl font-bold {{ $type === 'expense' ? 'text-[var(--flux-orange)]' : 'text-[var(--flux-green)]' }}">
                        R$ {{ number_format((float) $summary['total'], 2, ',', '.') }}
                    </p>
                </article>

                <article class="flux-kpi {{ $primaryKpiClass }}">
                    <p class="text-sm text-[var(--flux-muted)]">{{ $context['paid_card_label'] }}</p>
                    <p class="mt-1 text-4xl font-bold {{ $type === 'expense' ? 'text-[var(--flux-orange)]' : 'text-[var(--flux-green)]' }}">
                        R$ {{ number_format((float) $summary['paid'], 2, ',', '.') }}
                    </p>
                </article>

                <article class="flux-kpi flux-kpi-expense">
                    <p class="text-sm text-[var(--flux-muted)]">{{ $context['pending_card_label'] }}</p>
                    <p class="mt-1 text-4xl font-bold text-[var(--flux-orange)]">
                        R$ {{ number_format((float) $summary['pending'], 2, ',', '.') }}
                    </p>
                </article>
            </section>

            <section class="flux-table-wrap">
                <div class="overflow-x-auto">
                    <table class="flux-table">
                        <thead>
                            <tr>
                                <th>{{ strtoupper($dateColumnLabel) }}</th>
                                <th>{{ strtoupper($context['counterparty_label']) }}</th>
                                <th>VALOR</th>
                                <th>BANCO</th>
                                <th>{{ strtoupper($context['source_label']) }}</th>
                                <th>{{ strtoupper($context['type_label']) }}</th>
                                <th>STATUS</th>
                                <th>DESCRICAO DETALHADA</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($transactions as $transaction)
                                <tr>
                                    <td>
                                        {{ optional($transaction->payment_status === 'payable' ? $transaction->expected_payment_date : $transaction->payment_date)->format('d/m/Y') }}
                                    </td>
                                    <td>{{ $transaction->counterparty->name ?? '-' }}</td>
                                    <td>R$ {{ number_format((float) $transaction->amount, 2, ',', '.') }}</td>
                                    <td>{{ $transaction->bankAccount->name ?? '-' }}</td>
                                    <td>{{ $transaction->paymentMethod->name ?? '-' }}</td>
                                    <td>{{ $transaction->category->name ?? '-' }}</td>
                                    <td>
                                        @if ($transaction->payment_status === 'paid')
                                            <span class="rounded-full bg-[var(--flux-green-soft)] px-2 py-1 text-xs font-semibold text-[var(--flux-green)]">{{ $paidStatusLabel }}</span>
                                        @else
                                            <span class="rounded-full bg-[var(--flux-orange-soft)] px-2 py-1 text-xs font-semibold text-[var(--flux-orange)]">{{ $pendingStatusLabel }}</span>
                                        @endif
                                    </td>
                                    <td>{{ $transaction->transactionGroup->description ?? '-' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center text-sm text-[var(--flux-muted)]">
                                        {{ $context['empty_text'] }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>

            <div>
                {{ $transactions->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
