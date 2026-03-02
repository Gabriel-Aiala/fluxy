<x-app-layout>
    <div class="py-8">
        <div class="flux-shell">
            <section class="flux-card p-6 max-w-2xl space-y-4">
                <h1 class="font-display text-4xl font-bold text-[var(--flux-ink)]">Detalhes da Forma de Pagamento</h1>

                <div>
                    <p class="text-sm font-semibold text-[var(--flux-muted)]">ID</p>
                    <p class="text-lg font-semibold text-[var(--flux-ink)]">{{ $paymentMethod->id }}</p>
                </div>

                <div>
                    <p class="text-sm font-semibold text-[var(--flux-muted)]">Nome</p>
                    <p class="text-lg font-semibold text-[var(--flux-ink)]">{{ $paymentMethod->name }}</p>
                </div>

                <div class="flex items-center gap-2 pt-2">
                    <a href="{{ route('payment-methods.edit', $paymentMethod) }}" class="flux-primary-btn">Editar</a>
                    <a href="{{ route('payment-methods.index') }}" class="flux-secondary-btn">Voltar</a>
                </div>
            </section>
        </div>
    </div>
</x-app-layout>
