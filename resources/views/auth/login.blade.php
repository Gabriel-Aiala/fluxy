<x-guest-layout>
    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <div class="mb-6">
        <h1 class="text-2xl font-bold tracking-tight text-[var(--flux-dark)]">Entrar</h1>
        <p class="mt-1 text-sm text-[var(--flux-muted)]">Acesse sua conta para continuar no Lucre.</p>
    </div>

    <form method="POST" action="{{ route('login') }}" class="space-y-4">
        @csrf

        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="'E-mail'" />
            <x-text-input id="email" class="mt-1 block w-full rounded-xl border-[var(--flux-border)] focus:border-[var(--flux-dark)] focus:ring-[var(--flux-dark)]" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div>
            <x-input-label for="password" :value="'Senha'" />

            <x-text-input id="password" class="mt-1 block w-full rounded-xl border-[var(--flux-border)] focus:border-[var(--flux-dark)] focus:ring-[var(--flux-dark)]"
                            type="password"
                            name="password"
                            required autocomplete="current-password" />

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Remember Me -->
        <div class="block">
            <label for="remember_me" class="inline-flex items-center">
                <input id="remember_me" type="checkbox" class="rounded border-[var(--flux-border)] text-[var(--flux-dark)] shadow-sm focus:ring-[var(--flux-dark)]" name="remember">
                <span class="ms-2 text-sm text-[var(--flux-muted)]">Lembrar de mim</span>
            </label>
        </div>

        <div class="pt-2">
            <button type="submit" class="flux-primary-btn w-full justify-center">
                Entrar
            </button>
        </div>

        <div class="flex flex-col gap-2 border-t border-[var(--flux-border)] pt-4 text-sm">
            @if (Route::has('password.request'))
                <a class="font-medium text-[var(--flux-muted)] underline-offset-2 hover:underline hover:text-[var(--flux-dark)] focus:outline-none focus:ring-2 focus:ring-[var(--flux-dark)] focus:ring-offset-2" href="{{ route('password.request') }}">
                    Esqueceu sua senha?
                </a>
            @endif

            <a class="font-medium text-[var(--flux-muted)] underline-offset-2 hover:underline hover:text-[var(--flux-dark)] focus:outline-none focus:ring-2 focus:ring-[var(--flux-dark)] focus:ring-offset-2" href="{{ route('register') }}">
                Nao tem conta? Cadastre-se
            </a>
        </div>
    </form>
</x-guest-layout>
