<x-app-layout>
    <div class="py-8">
        <div class="flux-shell">
            <section class="flux-card p-6 max-w-2xl">
                <h1 class="font-display text-4xl font-bold text-[var(--flux-ink)]">Editar Forma de Pagamento</h1>

                <form method="POST" action="{{ route('payment-methods.update', $paymentMethod) }}" class="mt-5 space-y-4">
                    @csrf
                    @method('PUT')

                    <div>
                        <x-input-label for="name" :value="'Nome'" />
                        <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $paymentMethod->name)" required />
                        <x-input-error :messages="$errors->get('name')" class="mt-2" />
                    </div>

                    <div class="flex items-center gap-2">
                        <button type="submit" class="flux-primary-btn">Atualizar</button>
                        <a href="{{ route('payment-methods.index') }}" class="flux-secondary-btn">Cancelar</a>
                    </div>
                </form>
            </section>
        </div>
    </div>
</x-app-layout>
