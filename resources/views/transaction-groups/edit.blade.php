<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Editar Grupo de Transacao') }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <form method="POST" action="{{ route('transaction-groups.update', $transactionGroup->id) }}" class="space-y-4">
                        @csrf
                        @method('PUT')

                        <div>
                            <x-input-label for="organization_id" :value="__('Organizacao')" />
                            <select id="organization_id" name="organization_id"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                required>
                                <option value="">Selecione...</option>
                                @foreach ($organizations as $organization)
                                    <option value="{{ $organization->id }}"
                                        @selected(old('organization_id', $transactionGroup->organization_id) == $organization->id)>
                                        {{ $organization->name }}
                                    </option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('organization_id')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="type" :value="__('Tipo')" />
                            <select id="type" name="type"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                required>
                                <option value="">Selecione...</option>
                                <option value="income" @selected(old('type', $transactionGroup->type) === 'income')>income</option>
                                <option value="expense" @selected(old('type', $transactionGroup->type) === 'expense')>expense</option>
                            </select>
                            <x-input-error :messages="$errors->get('type')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="description" :value="__('Descricao')" />
                            <x-text-input id="description" name="description" type="text" class="mt-1 block w-full"
                                :value="old('description', $transactionGroup->description)" />
                            <x-input-error :messages="$errors->get('description')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="occurred_on" :value="__('Data da Ocorrencia')" />
                            <x-text-input id="occurred_on" name="occurred_on" type="date" class="mt-1 block w-full"
                                :value="old('occurred_on', $transactionGroup->occurred_on)" required />
                            <x-input-error :messages="$errors->get('occurred_on')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="customer_installments" :value="__('Parcelas Cliente')" />
                            <x-text-input id="customer_installments" name="customer_installments" type="number"
                                min="1" class="mt-1 block w-full"
                                :value="old('customer_installments', $transactionGroup->customer_installments)" required />
                            <x-input-error :messages="$errors->get('customer_installments')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="flow_installments" :value="__('Parcelas Fluxo')" />
                            <x-text-input id="flow_installments" name="flow_installments" type="number" min="1"
                                class="mt-1 block w-full" :value="old('flow_installments', $transactionGroup->flow_installments)"
                                required />
                            <x-input-error :messages="$errors->get('flow_installments')" class="mt-2" />
                        </div>

                        <div class="flex items-center gap-2">
                            <input id="anticipation" name="anticipation" type="checkbox" value="1"
                                @checked(old('anticipation', $transactionGroup->anticipation))
                                class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                            <label for="anticipation" class="text-sm text-gray-700 dark:text-gray-300">
                                Antecipacao
                            </label>
                        </div>
                        <x-input-error :messages="$errors->get('anticipation')" class="mt-2" />

                        <div class="flex items-center gap-3">
                            <a href="{{ route('transaction-groups.index') }}"
                                class="inline-flex items-center px-4 py-2 bg-gray-200 border border-transparent rounded-md font-semibold text-xs uppercase tracking-widest text-gray-700 hover:bg-gray-300">
                                Cancelar
                            </a>
                            <x-primary-button>
                                Atualizar
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
