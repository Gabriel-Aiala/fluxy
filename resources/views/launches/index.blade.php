<x-app-layout>
    <div class="py-8">
        <div class="flux-shell space-y-5">
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div>
                    <h1 class="font-display text-5xl font-bold text-[var(--flux-ink)]">Lancamentos</h1>
                    <p class="mt-1 text-lg text-[var(--flux-muted)]">Gerencie todas as suas receitas e despesas</p>
                </div>

                <div class="flex items-center gap-3">
                    @if ($canManageFinance)
                        <a href="{{ route('transactions.create') }}" class="flux-primary-btn">
                            <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path d="M10 4a1 1 0 011 1v4h4a1 1 0 110 2h-4v4a1 1 0 11-2 0v-4H5a1 1 0 110-2h4V5a1 1 0 011-1z" />
                            </svg>
                            Nova Transacao
                        </a>
                    @endif
                </div>
            </div>

            <section class="flux-card p-4">
                <form method="GET" action="{{ route('launches.index') }}"
                    class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-5">
                    <div>
                        <x-input-label for="type" :value="'Tipo'" />
                        <select id="type" name="type"
                            class="mt-1 block w-full rounded-xl border-[var(--flux-border)] bg-white/90 shadow-sm focus:border-[#17736a] focus:ring-[#17736a]">
                            <option value="">Todos</option>
                            @foreach ($typeOptions as $typeValue => $typeLabel)
                                <option value="{{ $typeValue }}" @selected(($filters['type'] ?? null) === $typeValue)>
                                    {{ $typeLabel }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <x-input-label for="payment_status" :value="'Status'" />
                        <select id="payment_status" name="payment_status"
                            class="mt-1 block w-full rounded-xl border-[var(--flux-border)] bg-white/90 shadow-sm focus:border-[#17736a] focus:ring-[#17736a]">
                            <option value="">Todos</option>
                            @foreach ($paymentStatusOptions as $statusValue => $statusLabel)
                                <option value="{{ $statusValue }}" @selected(($filters['payment_status'] ?? null) === $statusValue)>
                                    {{ $statusLabel }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <x-input-label for="payment_date_from" :value="'Pagamento de'" />
                        <x-text-input id="payment_date_from" name="payment_date_from" type="date" class="mt-1 block w-full"
                            :value="$filters['payment_date_from'] ?? ''" />
                    </div>
                    <div>
                        <x-input-label for="payment_date_to" :value="'Pagamento ate'" />
                        <x-text-input id="payment_date_to" name="payment_date_to" type="date" class="mt-1 block w-full"
                            :value="$filters['payment_date_to'] ?? ''" />
                    </div>
                    <div class="flex items-end gap-2">
                        <button type="submit" class="flux-primary-btn h-10">
                            Filtrar
                        </button>
                        <a href="{{ route('launches.index') }}" class="flux-secondary-btn h-10">
                            Limpar
                        </a>
                    </div>
                </form>
            </section>

            <section class="flux-table-wrap">
                <div class="overflow-x-auto">
                    <table class="flux-table w-full">
                        <thead>
                            <tr>
                                <th>Data</th>
                                <th>Descricao</th>
                                <th>Categoria</th>
                                <th>Tipo</th>
                                <th class="text-right">Valor</th>
                                <th>Status</th>
                                <th>Pagamento</th>
                                <th class="text-right">Acoes</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($transactions as $transaction)
                                @php($isIncome = $transaction->type === 'income')
                                @php($isPaid = $transaction->payment_status === 'paid')
                                <tr>
                                    <td class="font-semibold">
                                        {{ optional($transaction->payment_status === 'payable' ? $transaction->expected_payment_date : $transaction->payment_date)->format('d/m/Y') }}
                                    </td>
                                    <td>
                                        {{ $transaction->transactionGroup->description ?? '-' }}
                                    </td>
                                    <td>{{ $transaction->category->name ?? '-' }}</td>
                                    <td>
                                        <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-semibold {{ $isIncome ? 'bg-[var(--flux-green-soft)] text-[var(--flux-green)]' : 'bg-[var(--flux-orange-soft)] text-[var(--flux-orange)]' }}">
                                            {{ $isIncome ? 'Receita' : 'Despesa' }}
                                        </span>
                                    </td>
                                    <td class="text-right font-mono font-semibold {{ $isIncome ? 'text-[var(--flux-green)]' : 'text-[var(--flux-orange)]' }}">
                                        {{ $isIncome ? '+' : '-' }} R$ {{ number_format((float) $transaction->amount, 2, ',', '.') }}
                                    </td>
                                    <td>
                                        <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-semibold {{ $isPaid ? 'bg-[var(--flux-green-soft)] text-[var(--flux-green)]' : 'bg-[var(--flux-orange-soft)] text-[var(--flux-orange)]' }}">
                                            {{ $isPaid ? 'Pago' : 'Pendente' }}
                                        </span>
                                    </td>
                                    <td>{{ $transaction->paymentMethod->name ?? '-' }}</td>
                                    <td class="text-right">
                                        <div class="inline-flex items-center gap-3">
                                            @if ($canManageFinance)
                                                <a href="{{ route('transactions.edit', $transaction) }}"
                                                    class="text-sm font-semibold text-[var(--flux-muted)] hover:text-[var(--flux-ink)]">
                                                    Editar
                                                </a>
                                                <form method="POST"
                                                    action="{{ route('transactions.destroy', $transaction) }}"
                                                    onsubmit="return confirm('Excluir este lancamento?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit"
                                                        class="text-sm font-semibold text-red-600 hover:text-red-800">
                                                        Excluir
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-4 py-12 text-center text-sm text-[var(--flux-muted)]">
                                        Nenhum lancamento encontrado.
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
