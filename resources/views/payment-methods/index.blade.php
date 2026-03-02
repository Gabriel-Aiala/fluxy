<x-app-layout>
    <div class="py-8">
        <div class="flux-shell space-y-5">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h1 class="font-display text-5xl font-bold text-[var(--flux-ink)]">Formas de Pagamento</h1>
                    <p class="mt-1 text-lg text-[var(--flux-muted)]">Gerencie os metodos de pagamento disponiveis</p>
                </div>

                <a href="{{ route('payment-methods.create') }}" class="flux-primary-btn">
                    + Adicionar
                </a>
            </div>

            @if (session('success'))
                <section class="flux-card p-4 text-sm font-semibold text-[var(--flux-green)]">
                    {{ session('success') }}
                </section>
            @endif

            <section class="flux-table-wrap">
                <div class="overflow-x-auto">
                    <table class="flux-table w-full">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nome</th>
                                <th class="text-right">Acoes</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($paymentMethods as $paymentMethod)
                                <tr>
                                    <td>{{ $paymentMethod->id }}</td>
                                    <td>{{ $paymentMethod->name }}</td>
                                    <td class="text-right">
                                        <div class="inline-flex items-center gap-3">
                                            <a href="{{ route('payment-methods.show', $paymentMethod) }}"
                                                class="text-sm font-semibold text-[var(--flux-muted)] hover:text-[var(--flux-ink)]">
                                                Ver
                                            </a>
                                            <a href="{{ route('payment-methods.edit', $paymentMethod) }}"
                                                class="text-sm font-semibold text-[var(--flux-muted)] hover:text-[var(--flux-ink)]">
                                                Editar
                                            </a>
                                            <form method="POST" action="{{ route('payment-methods.destroy', $paymentMethod) }}"
                                                onsubmit="return confirm('Excluir esta forma de pagamento?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-sm font-semibold text-red-600 hover:text-red-800">
                                                    Excluir
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="px-4 py-12 text-center text-sm text-[var(--flux-muted)]">
                                        Nenhuma forma de pagamento cadastrada.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>

            <div>
                {{ $paymentMethods->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
