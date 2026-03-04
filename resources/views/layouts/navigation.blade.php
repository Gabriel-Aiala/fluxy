@php
    $authUser = Auth::user();

    $quickCreateLabel = 'Nova Transacao';
    $quickCreateHref = route('transactions.create');
    $currentType = request()->query('type');

    if (request()->routeIs('transactions.*') && in_array($currentType, ['income', 'expense'], true)) {
        $quickCreateLabel = $currentType === 'income' ? 'Nova Entrada' : 'Nova Saida';
        $quickCreateHref = route('transactions.create', ['type' => $currentType]);
    }

    $workspaceLinks = [
        ['label' => 'Dashboard', 'href' => route('dashboard'), 'active' => request()->routeIs('dashboard')],
        ['label' => 'Entradas', 'href' => route('transactions.index', ['type' => 'income']), 'active' => request()->routeIs('transactions.*') && request('type') === 'income'],
        ['label' => 'Saidas', 'href' => route('transactions.index', ['type' => 'expense']), 'active' => request()->routeIs('transactions.*') && request('type') === 'expense'],
        ['label' => 'Controle', 'href' => route('control.index'), 'active' => request()->routeIs('control.*')],
        ['label' => 'Fluxo Diario', 'href' => route('daily-flow.index'), 'active' => request()->routeIs('daily-flow.*')],
        ['label' => 'Banco', 'href' => route('bank.index'), 'active' => request()->routeIs('bank.*')],
        ['label' => 'Lancamentos', 'href' => route('launches.index'), 'active' => request()->routeIs('launches.*')],
        [
            'label' => 'Cadastros',
            'href' => route('registers.index'),
            'active' => request()->routeIs('registers.*')
                || request()->routeIs('categories.*')
                || request()->routeIs('counterparties.*')
                || request()->routeIs('bank-accounts.*')
                || request()->routeIs('payment-methods.*'),
        ],
    ];
@endphp

<nav x-data="{ open: false }" class="sticky top-0 z-40 border-b border-[var(--flux-border)] bg-white/95 backdrop-blur">
    <div class="flux-shell flex h-20 items-center justify-between gap-4">
        <div class="flex items-center gap-8">
            <a href="{{ route('dashboard') }}" class="inline-flex items-center gap-3">
                <span class="inline-flex h-12 w-12 items-center justify-center rounded-2xl bg-[var(--flux-dark)] text-white shadow-sm">
                    <x-application-logo class="h-6 w-6 fill-current" />
                </span>
                <span class="hidden flex-col leading-tight sm:inline-flex">
                    <span class="font-display text-4xl font-bold tracking-tight text-[var(--flux-dark)]">fluxy</span>
                    <span class="text-sm text-[var(--flux-muted)]">Sua vida financeira em fluxo</span>
                </span>
            </a>
        </div>

        <div class="hidden items-center gap-3 sm:flex">
            <a href="{{ $quickCreateHref }}" class="flux-primary-btn">
                <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path d="M10 4a1 1 0 011 1v4h4a1 1 0 110 2h-4v4a1 1 0 11-2 0v-4H5a1 1 0 110-2h4V5a1 1 0 011-1z" />
                </svg>
                {{ $quickCreateLabel }}
            </a>

            <x-dropdown align="right" width="48">
                <x-slot name="trigger">
                    <button class="inline-flex items-center gap-2 rounded-full border border-[var(--flux-border)] bg-white px-3 py-1.5 text-sm font-semibold text-[var(--flux-muted)] shadow-sm transition hover:border-[#9eb4a4] hover:text-[var(--flux-ink)]">
                        <span>{{ $authUser?->name }}</span>
                        <svg class="h-4 w-4 fill-current" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                        </svg>
                    </button>
                </x-slot>

                <x-slot name="content">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <x-dropdown-link :href="route('logout')"
                            onclick="event.preventDefault(); this.closest('form').submit();">
                            Sair
                        </x-dropdown-link>
                    </form>
                </x-slot>
            </x-dropdown>
        </div>

        <div class="flex items-center sm:hidden">
            <button @click="open = ! open" class="inline-flex items-center justify-center rounded-xl border border-[var(--flux-border)] bg-white p-2 text-[var(--flux-muted)] transition hover:bg-[#f3f4ef] hover:text-[var(--flux-ink)]">
                <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                    <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
    </div>

    <div class="border-t border-[var(--flux-border)] bg-white/90">
        <div class="flux-shell hidden items-center gap-1 overflow-x-auto py-2 sm:flex">
            @foreach ($workspaceLinks as $workspaceLink)
                <a href="{{ $workspaceLink['href'] }}"
                    class="{{ $workspaceLink['active'] ? 'flux-pill-nav flux-pill-nav-active' : 'flux-pill-nav' }}">
                    {{ $workspaceLink['label'] }}
                </a>
            @endforeach
        </div>
    </div>

    <div :class="{'block': open, 'hidden': ! open}" class="hidden border-t border-[var(--flux-border)] bg-white sm:hidden">
        <div class="space-y-1 px-4 py-3">
            @foreach ($workspaceLinks as $workspaceLink)
                <x-responsive-nav-link :href="$workspaceLink['href']" :active="$workspaceLink['active']">
                    {{ $workspaceLink['label'] }}
                </x-responsive-nav-link>
            @endforeach
        </div>

        <div class="border-t border-[var(--flux-border)] px-4 py-3">
            <div class="text-base font-semibold text-[var(--flux-ink)]">{{ $authUser?->name }}</div>
            <div class="text-sm text-[var(--flux-muted)]">{{ $authUser?->email }}</div>

            <a href="{{ $quickCreateHref }}" class="flux-primary-btn mt-3 w-full justify-center">
                {{ $quickCreateLabel }}
            </a>

            <div class="mt-3 space-y-1">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <x-responsive-nav-link :href="route('logout')"
                        onclick="event.preventDefault(); this.closest('form').submit();">
                        Sair
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>
