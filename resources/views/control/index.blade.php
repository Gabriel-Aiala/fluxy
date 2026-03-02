<x-app-layout>
    <div class="py-8">
        <div class="flux-shell space-y-6">
            <div>
                <h1 class="font-display text-4xl font-bold text-[var(--flux-ink)]">Controle</h1>
                <p class="mt-1 text-lg text-[var(--flux-muted)]">Visao do planejamento realizado x meta mensal</p>
            </div>

            <div class="grid grid-cols-1 gap-4 lg:grid-cols-4">
                @foreach ($cards as $card)
                    <article class="flux-card overflow-hidden">
                        <div class="px-4 py-3 text-center text-2xl font-bold uppercase tracking-wide {{ $card['is_current'] ? 'bg-[var(--flux-dark)] text-white' : 'bg-[#f2f3ef] text-[var(--flux-ink)]' }}">
                            {{ $card['label'] }} @if ($card['is_current'])
                                <span class="text-sm align-middle opacity-80">&bull; atual</span>
                            @endif
                        </div>
                        <div class="space-y-2 px-4 py-4 text-sm">
                            <div class="flex items-center justify-between">
                                <span class="font-semibold text-[var(--flux-green)]">Recebido</span>
                                <span class="font-mono font-semibold text-[var(--flux-ink)]">R$ {{ number_format($card['received'], 2, ',', '.') }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="font-semibold text-[var(--flux-orange)]">A Receber</span>
                                <span class="font-mono text-[var(--flux-muted)]">R$ {{ number_format($card['to_receive'], 2, ',', '.') }}</span>
                            </div>
                            <div class="mt-3 flex items-center justify-between">
                                <span class="font-semibold text-[var(--flux-orange)]">Pago</span>
                                <span class="font-mono font-semibold text-[var(--flux-ink)]">R$ {{ number_format($card['paid'], 2, ',', '.') }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="font-semibold text-[var(--flux-orange)]">A Pagar</span>
                                <span class="font-mono text-[var(--flux-muted)]">R$ {{ number_format($card['to_pay'], 2, ',', '.') }}</span>
                            </div>
                            <div class="mt-4 rounded-xl bg-[var(--flux-green-soft)] px-3 py-2">
                                <div class="flex items-center justify-between">
                                    <span class="font-semibold text-[var(--flux-green)]">Resultado</span>
                                    <span class="font-mono font-bold {{ $card['result'] >= 0 ? 'text-[var(--flux-green)]' : 'text-[var(--flux-orange)]' }}">
                                        R$ {{ number_format($card['result'], 2, ',', '.') }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>

            <section class="flux-table-wrap">
                <div class="overflow-x-auto">
                    <table class="min-w-[1100px] divide-y divide-[var(--flux-border)]">
                        <thead>
                            <tr class="text-center text-sm font-semibold uppercase tracking-wide text-[var(--flux-muted)]">
                                <th class="sticky left-0 z-10 w-72 border-r border-[var(--flux-border)] bg-[var(--flux-dark)] px-4 py-3 text-left text-white">
                                    Descricao
                                </th>
                                @foreach ($monthTabs as $month)
                                    <th class="border-l border-[var(--flux-border)] px-3 py-3 {{ $month['number'] === now()->month ? 'bg-[var(--flux-orange)] text-white' : 'bg-[#eceee8]' }}">
                                        {{ $month['short'] }}
                                    </th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($rows as $row)
                                @php
                                    $isSection = $row['type'] === 'section';
                                    $sectionClass = match ($row['variant'] ?? null) {
                                        'income' => 'bg-[var(--flux-green-soft)] text-[var(--flux-green)]',
                                        'fixed-expense' => 'bg-[var(--flux-orange-soft)] text-[var(--flux-orange)]',
                                        'common-expense' => 'bg-[#f4e8de] text-[var(--flux-orange)]',
                                        default => 'bg-white text-[var(--flux-muted)]',
                                    };
                                @endphp
                                <tr class="border-t border-[var(--flux-border)] {{ $isSection ? $sectionClass : 'bg-white/70 hover:bg-[#f4f5ef]' }}">
                                    <td class="sticky left-0 z-10 border-r border-[var(--flux-border)] px-4 py-3 font-semibold {{ $isSection ? $sectionClass : 'bg-white text-[var(--flux-muted)]' }}">
                                        {{ $row['label'] }}
                                    </td>
                                    @foreach ($monthTabs as $month)
                                        <td class="border-l border-[var(--flux-border)] px-2 py-3 text-center text-xs font-mono text-[var(--flux-ink)]">
                                            R$ {{ number_format($row['months'][$month['number']]['real'], 0, ',', '.') }}
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </div>
</x-app-layout>
