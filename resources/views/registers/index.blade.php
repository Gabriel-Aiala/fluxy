<x-app-layout>
    <div class="py-8">
        <div class="flux-shell space-y-5">
            <div>
                <h1 class="font-display text-5xl font-bold text-[var(--flux-ink)]">Cadastros</h1>
                <p class="mt-1 text-lg text-[var(--flux-muted)]">Gerencie suas categorias, bancos e formas de pagamento</p>
            </div>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5">
                <article class="flex min-h-[420px] flex-col overflow-hidden rounded-2xl border border-[var(--flux-green)] bg-[var(--flux-green-soft)]">
                    <header class="bg-[var(--flux-green)] px-4 py-3 text-center text-xl font-bold uppercase tracking-wide text-white">Entradas</header>
                    <div class="flex-1 space-y-2 p-3">
                        @forelse ($incomeCategories->take(8) as $category)
                            <div class="rounded-xl bg-white/70 px-3 py-2 text-sm font-semibold text-[var(--flux-ink)]">{{ $category->name }}</div>
                        @empty
                            <div class="rounded-xl bg-white/70 px-3 py-5 text-center text-sm text-[var(--flux-muted)]">Sem registros</div>
                        @endforelse
                    </div>
                    <div class="mt-auto grid grid-cols-2 gap-2 p-3 pt-0">
                        <a href="{{ route('categories.create', ['type' => 'income']) }}" class="flux-primary-btn w-full justify-center px-3">
                            + Adicionar
                        </a>
                        <a href="{{ route('categories.index', ['type' => 'income']) }}" class="flux-secondary-btn w-full justify-center px-3">
                            Lista
                        </a>
                    </div>
                </article>

                <article class="flex min-h-[420px] flex-col overflow-hidden rounded-2xl border border-[var(--flux-orange)] bg-[var(--flux-orange-soft)]">
                    <header class="bg-[var(--flux-orange)] px-4 py-3 text-center text-xl font-bold uppercase tracking-wide text-white">Despesas Fixas</header>
                    <div class="flex-1 space-y-2 p-3">
                        @forelse ($fixedExpenseCategories->take(8) as $category)
                            <div class="rounded-xl bg-white/70 px-3 py-2 text-sm font-semibold text-[var(--flux-ink)]">{{ $category->name }}</div>
                        @empty
                            <div class="rounded-xl bg-white/70 px-3 py-5 text-center text-sm text-[var(--flux-muted)]">Sem registros</div>
                        @endforelse
                    </div>
                    <div class="mt-auto grid grid-cols-2 gap-2 p-3 pt-0">
                        <a href="{{ route('categories.create', ['type' => 'expense', 'cost_type' => 'fixed']) }}" class="flux-primary-btn w-full justify-center px-3">
                            + Adicionar
                        </a>
                        <a href="{{ route('categories.index', ['type' => 'expense', 'cost_type' => 'fixed']) }}" class="flux-secondary-btn w-full justify-center px-3">
                            Lista
                        </a>
                    </div>
                </article>

                <article class="flex min-h-[420px] flex-col overflow-hidden rounded-2xl border border-[var(--flux-orange)] bg-[#f5e8dd]">
                    <header class="bg-[var(--flux-orange)] px-4 py-3 text-center text-xl font-bold uppercase tracking-wide text-white">Custos Variaveis</header>
                    <div class="flex-1 space-y-2 p-3">
                        @forelse ($commonExpenseCategories->take(8) as $category)
                            <div class="rounded-xl bg-white/70 px-3 py-2 text-sm font-semibold text-[var(--flux-ink)]">{{ $category->name }}</div>
                        @empty
                            <div class="rounded-xl bg-white/70 px-3 py-5 text-center text-sm text-[var(--flux-muted)]">Sem registros</div>
                        @endforelse
                    </div>
                    <div class="mt-auto grid grid-cols-2 gap-2 p-3 pt-0">
                        <a href="{{ route('categories.create', ['type' => 'expense', 'cost_type' => 'variable']) }}" class="flux-primary-btn w-full justify-center px-3">
                            + Adicionar
                        </a>
                        <a href="{{ route('categories.index', ['type' => 'expense', 'cost_type' => 'variable']) }}" class="flux-secondary-btn w-full justify-center px-3">
                            Lista
                        </a>
                    </div>
                </article>

                <article class="flex min-h-[420px] flex-col overflow-hidden rounded-2xl border border-[#0a4a44] bg-[var(--flux-neutral-soft)]">
                    <header class="bg-[#013f39] px-4 py-3 text-center text-xl font-bold uppercase tracking-wide text-white">Banco</header>
                    <div class="flex-1 space-y-2 p-3">
                        @forelse ($bankAccounts->take(8) as $bankAccount)
                            <div class="rounded-xl bg-white/70 px-3 py-2 text-sm font-semibold text-[var(--flux-ink)]">{{ $bankAccount->name }}</div>
                        @empty
                            <div class="rounded-xl bg-white/70 px-3 py-5 text-center text-sm text-[var(--flux-muted)]">Sem registros</div>
                        @endforelse
                    </div>
                    <div class="mt-auto grid grid-cols-2 gap-2 p-3 pt-0">
                        <a href="{{ route('bank-accounts.create') }}" class="flux-primary-btn w-full justify-center px-3">
                            + Adicionar
                        </a>
                        <a href="{{ route('bank-accounts.index') }}" class="flux-secondary-btn w-full justify-center px-3">
                            Lista
                        </a>
                    </div>
                </article>

                <article class="flex min-h-[420px] flex-col overflow-hidden rounded-2xl border border-[#0a4a44] bg-[var(--flux-neutral-soft)]">
                    <header class="bg-[#013f39] px-4 py-3 text-center text-xl font-bold uppercase tracking-wide text-white">
                        Forma de Pagamento
                    </header>
                    <div class="flex-1 space-y-2 p-3">
                        @forelse ($paymentMethods->take(8) as $paymentMethod)
                            <div class="rounded-xl bg-white/70 px-3 py-2 text-sm font-semibold text-[var(--flux-ink)]">{{ $paymentMethod->name }}</div>
                        @empty
                            <div class="rounded-xl bg-white/70 px-3 py-5 text-center text-sm text-[var(--flux-muted)]">Sem registros</div>
                        @endforelse
                    </div>
                    <div class="mt-auto grid grid-cols-2 gap-2 p-3 pt-0">
                        <a href="{{ route('payment-methods.create') }}" class="flux-primary-btn w-full justify-center px-3">
                            + Adicionar
                        </a>
                        <a href="{{ route('payment-methods.index') }}" class="flux-secondary-btn w-full justify-center px-3">
                            Lista
                        </a>
                    </div>
                </article>
            </div>
        </div>
    </div>
</x-app-layout>
