<x-guest-layout>
    <div class="mb-6">
        <h1 class="text-2xl font-bold tracking-tight text-[var(--flux-dark)]">Criar conta</h1>
        <p class="mt-1 text-sm text-[var(--flux-muted)]">Cadastre-se para comecar a usar o Lucre.</p>
    </div>

    <form method="POST" action="{{ route('register') }}" class="space-y-4">
        @csrf

        <!-- Organization Name -->
        <div>
            <x-input-label for="organization_name" :value="'Nome da empresa'" />
            <x-text-input id="organization_name" class="mt-1 block w-full rounded-xl border-[var(--flux-border)] focus:border-[var(--flux-dark)] focus:ring-[var(--flux-dark)]" type="text" name="organization_name" :value="old('organization_name')" required autofocus autocomplete="organization" />
            <x-input-error :messages="$errors->get('organization_name')" class="mt-2" />
        </div>

        <!-- Organization CNPJ -->
        <div>
            <x-input-label for="organization_cnpj" :value="'CNPJ da empresa'" />
            <x-text-input id="organization_cnpj" class="mt-1 block w-full rounded-xl border-[var(--flux-border)] focus:border-[var(--flux-dark)] focus:ring-[var(--flux-dark)]" type="text" name="organization_cnpj" :value="old('organization_cnpj')" required autocomplete="organization-tax-id" />
            <x-input-error :messages="$errors->get('organization_cnpj')" class="mt-2" />
        </div>

        <!-- Name -->
        <div>
            <x-input-label for="name" :value="'Seu nome'" />
            <x-text-input id="name" class="mt-1 block w-full rounded-xl border-[var(--flux-border)] focus:border-[var(--flux-dark)] focus:ring-[var(--flux-dark)]" type="text" name="name" :value="old('name')" required autocomplete="name" />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="'E-mail'" />
            <x-text-input id="email" class="mt-1 block w-full rounded-xl border-[var(--flux-border)] focus:border-[var(--flux-dark)] focus:ring-[var(--flux-dark)]" type="email" name="email" :value="old('email')" required autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div>
            <x-input-label for="password" :value="'Senha'" />

            <x-text-input id="password" class="mt-1 block w-full rounded-xl border-[var(--flux-border)] focus:border-[var(--flux-dark)] focus:ring-[var(--flux-dark)]"
                            type="password"
                            name="password"
                            required autocomplete="new-password" />

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Confirm Password -->
        <div>
            <x-input-label for="password_confirmation" :value="'Confirmar senha'" />

            <x-text-input id="password_confirmation" class="mt-1 block w-full rounded-xl border-[var(--flux-border)] focus:border-[var(--flux-dark)] focus:ring-[var(--flux-dark)]"
                            type="password"
                            name="password_confirmation" required autocomplete="new-password" />

            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="pt-2">
            <button type="submit" class="flux-primary-btn w-full justify-center">
                Cadastrar
            </button>
        </div>

        <div class="border-t border-[var(--flux-border)] pt-4 text-sm">
            <a class="font-medium text-[var(--flux-muted)] underline-offset-2 hover:underline hover:text-[var(--flux-dark)] focus:outline-none focus:ring-2 focus:ring-[var(--flux-dark)] focus:ring-offset-2" href="{{ route('login') }}">
                Ja tem conta? Entrar
            </a>
        </div>
    </form>
</x-guest-layout>
