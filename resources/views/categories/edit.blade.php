<x-app-layout>
    @php
        $selectedType = old('type', $category->type);
        $selectedCostType = old('cost_type', $category->cost_type);
        $showCostType = $selectedType === 'expense';
        $inputClass = 'mt-1 block w-full rounded-xl border-[var(--flux-border)] bg-white/90 shadow-sm focus:border-[#17736a] focus:ring-[#17736a]';
    @endphp

    <div class="py-8">
        <div class="flux-shell space-y-6">
            <div>
                <h1 class="font-display text-4xl font-bold text-[var(--flux-ink)]">Editar categoria</h1>
                <p class="mt-1 text-lg text-[var(--flux-muted)]">Atualize nome, tipo e classificacao de custo da categoria.</p>
            </div>

            <section class="flux-card overflow-hidden">
                <div class="border-b border-[var(--flux-border)] bg-[#f4f6f1] px-6 py-4 text-sm text-[var(--flux-muted)]">
                    A organizacao e mantida automaticamente pelo sistema.
                </div>

                <form method="POST" action="{{ route('categories.update', $category->id) }}" class="space-y-5 p-6">
                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div>
                            <x-input-label :value="'Organizacao'" />
                            <p class="mt-1 rounded-xl border border-[var(--flux-border)] bg-[#f8f9f5] px-3 py-2 text-sm font-medium text-[var(--flux-ink)]">
                                {{ $organizationName }}
                            </p>
                        </div>
                        <div>
                            <x-input-label for="name" :value="'Nome da categoria'" />
                            <x-text-input id="name" name="name" type="text" class="{{ $inputClass }}" :value="old('name', $category->name)" required />
                            <x-input-error :messages="$errors->get('name')" class="mt-2" />
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div>
                            <x-input-label for="type" :value="'Tipo'" />
                            <select id="type" name="type" class="{{ $inputClass }}" required>
                                <option value="">Selecione</option>
                                <option value="income" @selected($selectedType === 'income')>Receita</option>
                                <option value="expense" @selected($selectedType === 'expense')>Despesa</option>
                            </select>
                            <x-input-error :messages="$errors->get('type')" class="mt-2" />
                        </div>

                        <div id="cost_type_wrapper" @class(['hidden' => ! $showCostType])>
                            <x-input-label for="cost_type" :value="'Tipo de custo'" />
                            <select id="cost_type" name="cost_type" class="{{ $inputClass }}">
                                <option value="">Selecione</option>
                                <option value="fixed" @selected($selectedCostType === 'fixed')>Fixo</option>
                                <option value="variable" @selected($selectedCostType === 'variable')>Variavel</option>
                                <option value="income" hidden @selected($selectedCostType === 'income')>Receita</option>
                            </select>
                            <x-input-error :messages="$errors->get('cost_type')" class="mt-2" />
                        </div>
                    </div>

                    <div class="flex flex-wrap items-center gap-3 pt-2">
                        <button type="submit" class="flux-primary-btn">Atualizar categoria</button>
                        <a href="{{ route('categories.index') }}" class="flux-secondary-btn">Voltar</a>
                    </div>
                </form>
            </section>
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
