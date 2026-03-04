<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Grupos de Transacao') }}
        </h2>
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
                                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider">Tipo</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider">Descricao</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider">Ocorrencia</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider">Parcelas</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider">Antecipacao</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider">Organizacao</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider">Detalhes</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                @forelse ($transactionGroups as $transactionGroup)
                                    <tr>
                                        <td class="px-4 py-3">{{ $transactionGroup->id }}</td>
                                        <td class="px-4 py-3">{{ $transactionGroup->type }}</td>
                                        <td class="px-4 py-3">{{ $transactionGroup->description ?? '-' }}</td>
                                        <td class="px-4 py-3">{{ $transactionGroup->occurred_on }}</td>
                                        <td class="px-4 py-3">
                                            {{ $transactionGroup->customer_installments }}/{{ $transactionGroup->flow_installments }}
                                            ({{ $transactionGroup->transactions_count }})
                                        </td>
                                        <td class="px-4 py-3">{{ $transactionGroup->anticipation ? 'sim' : 'nao' }}</td>
                                        <td class="px-4 py-3">{{ $transactionGroup->organization->name ?? '-' }}</td>
                                        <td class="px-4 py-3">
                                            <a href="{{ route('transaction-groups.show', $transactionGroup->id) }}"
                                                class="text-blue-600 hover:text-blue-500">Ver</a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="px-4 py-6 text-center text-gray-500">
                                            Nenhum grupo de transacao cadastrado.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-6">
                        {{ $transactionGroups->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
