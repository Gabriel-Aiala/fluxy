<x-app-layout>
    @php
        $inputClass = 'mt-1 block w-full rounded-xl border-[var(--flux-border)] bg-white/90 shadow-sm focus:border-[#17736a] focus:ring-[#17736a]';
    @endphp

    <div class="py-8">
        <div class="flux-shell space-y-6">
            <div>
                <h1 class="font-display text-4xl font-bold text-[var(--flux-ink)]">Nova conta bancaria</h1>
                <p class="mt-1 text-lg text-[var(--flux-muted)]">Cadastre uma conta para classificar recebimentos e pagamentos.</p>
            </div>

            <section class="flux-card overflow-hidden">
                <div class="border-b border-[var(--flux-border)] bg-[#f4f6f1] px-6 py-4 text-sm text-[var(--flux-muted)]">
                    A conta sera vinculada automaticamente a sua organizacao.
                </div>

                <form method="POST" action="{{ route('bank-accounts.store') }}" class="space-y-5 p-6">
                    @csrf

                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div>
                            <x-input-label :value="'Organizacao'" />
                            <p class="mt-1 rounded-xl border border-[var(--flux-border)] bg-[#f8f9f5] px-3 py-2 text-sm font-medium text-[var(--flux-ink)]">
                                {{ $organizationName }}
                            </p>
                        </div>
                        <div>
                            <x-input-label for="name" :value="'Nome da conta'" />
                            <x-text-input id="name" name="name" type="text" class="{{ $inputClass }}" :value="old('name')" required />
                            <x-input-error :messages="$errors->get('name')" class="mt-2" />
                        </div>
                    </div>

                    <div class="flex flex-wrap items-center gap-3 pt-2">
                        <button type="submit" class="flux-primary-btn">Salvar conta</button>
                        <a href="{{ route('bank-accounts.index') }}" class="flux-secondary-btn">Voltar</a>
                    </div>
                </form>
            </section>
        </div>
    </div>
</x-app-layout>
