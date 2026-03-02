<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Editar Categoria') }}
        </h2>
    </x-slot>

    @php
        $selectedType = old('type', $category->type);
        $showCostType = $selectedType === 'expense';
    @endphp

    <div class="py-8">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <form method="POST" action="{{ route('categories.update', $category->id) }}" class="space-y-4">
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
                                        @selected(old('organization_id', $category->organization_id) == $organization->id)>
                                        {{ $organization->name }}
                                    </option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('organization_id')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="name" :value="__('Nome')" />
                            <x-text-input id="name" name="name" type="text" class="mt-1 block w-full"
                                :value="old('name', $category->name)" required />
                            <x-input-error :messages="$errors->get('name')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="type" :value="__('Tipo')" />
                            <select id="type" name="type"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                required>
                                <option value="">Selecione...</option>
                                <option value="income" @selected($selectedType === 'income')>Receita</option>
                                <option value="expense" @selected($selectedType === 'expense')>Despesa</option>
                            </select>
                            <x-input-error :messages="$errors->get('type')" class="mt-2" />
                        </div>

                        <div id="cost_type_wrapper" @class(['hidden' => ! $showCostType])>
                            <x-input-label for="cost_type" :value="__('Tipo de Custo')" />
                            <select id="cost_type" name="cost_type"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">Selecione...</option>
                                <option value="fixed" @selected(old('cost_type', $category->cost_type) === 'fixed')>Fixo</option>
                                <option value="variable" @selected(old('cost_type', $category->cost_type) === 'variable')>Variavel</option>
                                <option value="income" hidden @selected(old('cost_type', $category->cost_type) === 'income')>Receita</option>
                            </select>
                            <x-input-error :messages="$errors->get('cost_type')" class="mt-2" />
                        </div>

                        <div class="flex items-center gap-3">
                            <a href="{{ route('categories.index') }}"
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

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const typeSelect = document.getElementById('type');
            const costTypeWrapper = document.getElementById('cost_type_wrapper');
            const costTypeSelect = document.getElementById('cost_type');

            if (!typeSelect || !costTypeWrapper || !costTypeSelect) {
                return;
            }

            const syncCostTypeVisibility = () => {
                const isExpense = typeSelect.value === 'expense';
                costTypeWrapper.classList.toggle('hidden', !isExpense);
                costTypeSelect.required = isExpense;
                costTypeSelect.disabled = !isExpense;

                if (!isExpense) {
                    costTypeSelect.value = 'income';
                } else if (!['fixed', 'variable'].includes(costTypeSelect.value)) {
                    costTypeSelect.value = 'fixed';
                }
            };

            typeSelect.addEventListener('change', syncCostTypeVisibility);
            syncCostTypeVisibility();
        });
    </script>
</x-app-layout>
