<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Detalhes da Transacao') }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100 grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <p class="text-sm text-gray-500">ID</p>
                        <p class="font-medium">{{ $transaction->id }}</p>
                    </div>

                    <div>
                        <p class="text-sm text-gray-500">Organizacao</p>
                        <p class="font-medium">{{ $transaction->organization->name ?? '-' }}</p>
                    </div>

                    <div>
                        <p class="text-sm text-gray-500">Grupo de Transacao</p>
                        <p class="font-medium">#{{ $transaction->transactionGroup->id ?? '-' }}</p>
                    </div>

                    <div>
                        <p class="text-sm text-gray-500">Conta Bancaria</p>
                        <p class="font-medium">{{ $transaction->bankAccount->name ?? '-' }}</p>
                    </div>

                    <div>
                        <p class="text-sm text-gray-500">Metodo de Pagamento</p>
                        <p class="font-medium">{{ $transaction->paymentMethod->name ?? '-' }}</p>
                    </div>

                    <div>
                        <p class="text-sm text-gray-500">Contraparte</p>
                        <p class="font-medium">{{ $transaction->counterparty->name ?? '-' }}</p>
                    </div>

                    <div>
                        <p class="text-sm text-gray-500">Categoria</p>
                        <p class="font-medium">{{ $transaction->category->name ?? '-' }}</p>
                    </div>

                    <div>
                        <p class="text-sm text-gray-500">Parcela</p>
                        <p class="font-medium">{{ $transaction->installment_number }}</p>
                    </div>

                    <div>
                        <p class="text-sm text-gray-500">Data Prevista</p>
                        <p class="font-medium">{{ $transaction->expected_payment_date?->format('Y-m-d') }}</p>
                    </div>

                    <div>
                        <p class="text-sm text-gray-500">Data de Pagamento</p>
                        <p class="font-medium">{{ $transaction->payment_date?->format('Y-m-d') }}</p>
                    </div>

                    <div>
                        <p class="text-sm text-gray-500">Valor</p>
                        <p class="font-medium">R$ {{ number_format((float) $transaction->amount, 2, ',', '.') }}</p>
                    </div>

                    <div>
                        <p class="text-sm text-gray-500">Status de Pagamento</p>
                        <p class="font-medium">{{ $transaction->payment_status }}</p>
                    </div>

                    <div>
                        <p class="text-sm text-gray-500">Tipo de Gasto</p>
                        <p class="font-medium">{{ $transaction->expense_type }}</p>
                    </div>

                    <div>
                        <p class="text-sm text-gray-500">Tipo</p>
                        <p class="font-medium">{{ $transaction->type }}</p>
                    </div>

                    <div class="md:col-span-2 flex items-center gap-3 pt-2">
                        <a href="{{ route('transactions.index') }}"
                            class="inline-flex items-center px-4 py-2 bg-gray-200 border border-transparent rounded-md font-semibold text-xs uppercase tracking-widest text-gray-700 hover:bg-gray-300">
                            Voltar
                        </a>
                        <a href="{{ route('transactions.edit', $transaction->id) }}"
                            class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs uppercase tracking-widest text-white hover:bg-indigo-500">
                            Editar
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
