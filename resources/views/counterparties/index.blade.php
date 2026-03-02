<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Contrapartes') }}
            </h2>

            <a href="{{ route('counterparties.create') }}"
                class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-500">
                Nova Contraparte
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    @if (session('success'))
                        <div class="mb-4 rounded-md bg-green-100 text-green-800 px-4 py-2">
                            {{ session('success') }}
                        </div>
                    @endif

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead>
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider">ID</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider">Nome</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider">Tipo</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider">Organizacao</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider">Acoes</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                @forelse ($counterparties as $counterparty)
                                    <tr>
                                        <td class="px-4 py-3">{{ $counterparty->id }}</td>
                                        <td class="px-4 py-3">{{ $counterparty->name }}</td>
                                        <td class="px-4 py-3">{{ $counterparty->type }}</td>
                                        <td class="px-4 py-3">{{ $counterparty->organization->name ?? '-' }}</td>
                                        <td class="px-4 py-3">
                                            <div class="flex items-center gap-3">
                                                <a href="{{ route('counterparties.show', $counterparty->id) }}"
                                                    class="text-blue-600 hover:text-blue-500">Ver</a>
                                                <a href="{{ route('counterparties.edit', $counterparty->id) }}"
                                                    class="text-indigo-600 hover:text-indigo-500">Editar</a>
                                                <form action="{{ route('counterparties.destroy', $counterparty->id) }}" method="POST"
                                                    onsubmit="return confirm('Deseja remover esta contraparte?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-red-600 hover:text-red-500">Excluir</button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-4 py-6 text-center text-gray-500">
                                            Nenhuma contraparte cadastrada.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-6">
                        {{ $counterparties->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
