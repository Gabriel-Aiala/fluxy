<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Detalhes da Conta Bancaria') }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100 space-y-4">
                    <div>
                        <p class="text-sm text-gray-500">ID</p>
                        <p class="font-medium">{{ $bankAccount->id }}</p>
                    </div>

                    <div>
                        <p class="text-sm text-gray-500">Nome</p>
                        <p class="font-medium">{{ $bankAccount->name }}</p>
                    </div>

                    <div>
                        <p class="text-sm text-gray-500">Organizacao</p>
                        <p class="font-medium">{{ $bankAccount->organization->name ?? '-' }}</p>
                    </div>

                    <div class="flex items-center gap-3 pt-2">
                        <a href="{{ route('bank-accounts.index') }}"
                            class="inline-flex items-center px-4 py-2 bg-gray-200 border border-transparent rounded-md font-semibold text-xs uppercase tracking-widest text-gray-700 hover:bg-gray-300">
                            Voltar
                        </a>
                        <a href="{{ route('bank-accounts.edit', $bankAccount->id) }}"
                            class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs uppercase tracking-widest text-white hover:bg-indigo-500">
                            Editar
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
