
<x-app-layout>
    @php
        $oldType = old('type', $defaultType ?: 'expense');
        $oldPaymentStatus = old('payment_status', 'payable');
        $inputClass = 'mt-1 block w-full rounded-xl border-[var(--flux-border)] bg-white/90 shadow-sm focus:border-[#17736a] focus:ring-[#17736a]';
        $linkClass = 'text-xs font-semibold text-[var(--flux-muted)] transition hover:text-[var(--flux-ink)]';
        $counterpartyLabel = $oldType === 'income' ? 'Cliente' : ($oldType === 'expense' ? 'Fornecedor' : 'Contraparte');
        $counterpartyActionLabel = $oldType === 'income' ? '+ Criar cliente' : ($oldType === 'expense' ? '+ Criar fornecedor' : '+ Criar contraparte');
        $counterpartyPlaceholder = $oldType === 'income' ? 'Selecione o cliente' : ($oldType === 'expense' ? 'Selecione o fornecedor' : 'Selecione');
        $counterpartyModalTitle = $oldType === 'income' ? 'Novo cliente' : ($oldType === 'expense' ? 'Novo fornecedor' : 'Nova contraparte');
        $counterpartyNameLabel = $oldType === 'income' ? 'Nome do cliente' : ($oldType === 'expense' ? 'Nome do fornecedor' : 'Nome');
        $counterpartyDefaultType = $oldType === 'income' ? 'client' : 'supplier';
        $paymentStatusLabel = $oldType === 'income' ? 'Status de recebimento' : 'Status de pagamento';
        $payableLabel = $oldType === 'income' ? 'A receber' : 'A pagar';
        $paidLabel = $oldType === 'income' ? 'Recebido' : 'Pago';
        $expenseTypeLabel = $oldType === 'income'
            ? 'Tipo de receita pessoal/profissional'
            : 'Tipo de despesa pessoal/profissional';
        $paymentDateLabel = $oldType === 'income' ? 'Data de recebimento' : 'Data de pagamento';
        $showPaymentDateField = $oldPaymentStatus === 'paid';
    @endphp

    <div class="py-8">
        <div class="flux-shell space-y-6">
            <div>
                <h1 class="font-display text-4xl font-bold text-[var(--flux-ink)]">Nova transacao</h1>
                <p class="mt-1 text-lg text-[var(--flux-muted)]">Cadastre uma receita ou despesa com data e status de pagamento.</p>
            </div>

            <section class="flux-card overflow-hidden">
                <div class="border-b border-[var(--flux-border)] bg-[#f4f6f1] px-6 py-4 text-sm text-[var(--flux-muted)]">
                    Todo campo de lista pode ser criado na hora pelo botao "+ Criar".
                </div>

                <form method="POST" action="{{ route('transactions.store') }}" class="space-y-5 p-6">
                    @csrf

                    <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                        <div>
                            <x-input-label for="type" :value="'Tipo da transacao'" />
                            <select id="type" name="type" class="{{ $inputClass }}" required>
                                <option value="">Selecione</option>
                                <option value="income" @selected($oldType === 'income')>Receita</option>
                                <option value="expense" @selected($oldType === 'expense')>Despesa</option>
                            </select>
                            <x-input-error :messages="$errors->get('type')" class="mt-2" />
                        </div>
                        <div>
                            <x-input-label id="payment-status-label" for="payment_status" :value="$paymentStatusLabel" />
                            <select id="payment_status" name="payment_status" class="{{ $inputClass }}" required>
                                <option value="">Selecione</option>
                                <option id="payment-status-payable-option" value="payable" @selected($oldPaymentStatus === 'payable')>{{ $payableLabel }}</option>
                                <option id="payment-status-paid-option" value="paid" @selected($oldPaymentStatus === 'paid')>{{ $paidLabel }}</option>
                            </select>
                            <x-input-error :messages="$errors->get('payment_status')" class="mt-2" />
                        </div>
                        <div>
                            <x-input-label id="expense-type-label" for="expense_type" :value="$expenseTypeLabel" />
                            <select id="expense_type" name="expense_type" class="{{ $inputClass }}" required>
                                <option value="">Selecione</option>
                                <option value="professional" @selected(old('expense_type') === 'professional')>Profissional</option>
                                <option value="personal" @selected(old('expense_type') === 'personal')>Pessoal</option>
                            </select>
                            <x-input-error :messages="$errors->get('expense_type')" class="mt-2" />
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                        <div>
                            <div class="flex items-center justify-between gap-2">
                                <x-input-label for="category_id" :value="'Categoria'" />
                                <button type="button" x-data x-on:click="$dispatch('open-modal', 'qc-category')" class="{{ $linkClass }}">+ Criar categoria</button>
                            </div>
                            <select id="category_id" name="category_id" class="{{ $inputClass }}" required>
                                <option id="category-placeholder-option" value="">Selecione</option>
                                @foreach ($categories as $category)
                                    <option data-category-type="{{ $category->type }}" value="{{ $category->id }}" @selected(old('category_id') == $category->id)>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('category_id')" class="mt-2" />
                        </div>
                        <div>
                            <x-input-label for="amount" :value="'Valor (total)'" />
                            <x-text-input id="amount" name="amount" type="number" step="0.01" min="0" class="{{ $inputClass }}" :value="old('amount')" required />
                            <x-input-error :messages="$errors->get('amount')" class="mt-2" />
                            <p class="mt-1 text-xs text-[var(--flux-muted)]">O valor total sera dividido automaticamente pelas parcelas.</p>
                        </div>
                        <div>
                            <x-input-label for="installment_number" :value="'Quantidade de parcelas'" />
                            <x-text-input id="installment_number" name="installment_number" type="number" min="1" class="{{ $inputClass }}" :value="old('installment_number', 1)" required />
                            <x-input-error :messages="$errors->get('installment_number')" class="mt-2" />
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div id="payment-date-field-wrap">
                            <x-input-label id="payment-date-label" for="payment_date" :value="$paymentDateLabel" />
                            <input id="payment_date" name="payment_date" type="date"
                                class="{{ $inputClass }} {{ ! $showPaymentDateField ? 'bg-gray-50 cursor-not-allowed' : '' }}"
                                value="{{ old('payment_date') }}"
                                @required($showPaymentDateField)
                                @readonly(! $showPaymentDateField)>
                            <x-input-error :messages="$errors->get('payment_date')" class="mt-2" />
                        </div>
                        <div>
                            <x-input-label for="expected_payment_date" :value="'Data esperada'" />
                            <x-text-input id="expected_payment_date" name="expected_payment_date" type="date" class="{{ $inputClass }}" :value="old('expected_payment_date')" required />
                            <x-input-error :messages="$errors->get('expected_payment_date')" class="mt-2" />
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div>
                            <div class="flex items-center justify-between gap-2">
                                <x-input-label for="bank_account_id" :value="'Conta bancaria'" />
                                <button type="button" x-data x-on:click="$dispatch('open-modal', 'qc-bank-account')" class="{{ $linkClass }}">+ Criar conta</button>
                            </div>
                            <select id="bank_account_id" name="bank_account_id" class="{{ $inputClass }}" required>
                                <option value="">Selecione</option>
                                @foreach ($bankAccounts as $bankAccount)
                                    <option value="{{ $bankAccount->id }}" @selected(old('bank_account_id') == $bankAccount->id)>{{ $bankAccount->name }} - {{ $bankAccount->organization->name ?? '-' }}</option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('bank_account_id')" class="mt-2" />
                        </div>
                        <div>
                            <div class="flex items-center justify-between gap-2">
                                <x-input-label for="payment_method_id" :value="'Metodo de pagamento'" />
                                <button type="button" x-data x-on:click="$dispatch('open-modal', 'qc-payment-method')" class="{{ $linkClass }}">+ Criar metodo</button>
                            </div>
                            <select id="payment_method_id" name="payment_method_id" class="{{ $inputClass }}" required>
                                <option value="">Selecione</option>
                                @foreach ($paymentMethods as $paymentMethod)
                                    <option value="{{ $paymentMethod->id }}" @selected(old('payment_method_id') == $paymentMethod->id)>{{ $paymentMethod->name }}</option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('payment_method_id')" class="mt-2" />
                        </div>
                    </div>

                    <div>
                        <div class="flex items-center justify-between gap-2">
                            <x-input-label id="counterparty-field-label" for="counterparty_id" :value="$counterpartyLabel" />
                            <button id="counterparty-create-trigger" type="button" x-data x-on:click="$dispatch('open-modal', 'qc-counterparty')" class="{{ $linkClass }}">{{ $counterpartyActionLabel }}</button>
                        </div>
                        <select id="counterparty_id" name="counterparty_id" class="{{ $inputClass }}" required>
                            <option id="counterparty-placeholder-option" value="">{{ $counterpartyPlaceholder }}</option>
                            @foreach ($counterparties as $counterparty)
                                <option data-counterparty-type="{{ $counterparty->type }}" value="{{ $counterparty->id }}" @selected(old('counterparty_id') == $counterparty->id)>{{ $counterparty->name }} ({{ $counterparty->type_label }}) - {{ $counterparty->organization->name ?? '-' }}</option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('counterparty_id')" class="mt-2" />
                    </div>

                    <div class="flex flex-wrap items-center gap-3 pt-2">
                        <button type="submit" class="flux-primary-btn">Criar transacao</button>
                        <a href="{{ route('transactions.index', ['type' => $defaultType]) }}" class="flux-secondary-btn">Voltar</a>
                    </div>
                </form>
            </section>
        </div>
    </div>
    <x-modal name="qc-bank-account" maxWidth="md">
        <form method="POST" action="{{ route('quick-create.bank-accounts') }}" data-quick-create data-target-select="bank_account_id" data-modal="qc-bank-account" class="space-y-4 p-6">
            @csrf
            <h3 class="font-display text-2xl font-bold text-[var(--flux-ink)]">Nova conta bancaria</h3>
            <div data-form-errors class="hidden rounded-xl bg-red-50 px-3 py-2 text-sm text-red-700"></div>
            <div>
                <x-input-label for="qc_bank_account_name" :value="'Nome da conta'" />
                <x-text-input id="qc_bank_account_name" name="name" class="{{ $inputClass }}" required />
                <p data-error-for="name" class="mt-1 hidden text-xs text-red-600"></p>
            </div>
            <div class="flex items-center gap-2 pt-2">
                <button type="submit" class="flux-primary-btn">Salvar</button>
                <button type="button" x-data x-on:click="$dispatch('close-modal', 'qc-bank-account')" class="flux-secondary-btn">Cancelar</button>
            </div>
        </form>
    </x-modal>
    <x-modal name="qc-payment-method" maxWidth="md">
        <form method="POST" action="{{ route('quick-create.payment-methods') }}" data-quick-create data-target-select="payment_method_id" data-modal="qc-payment-method" class="space-y-4 p-6">
            @csrf
            <h3 class="font-display text-2xl font-bold text-[var(--flux-ink)]">Novo metodo de pagamento</h3>
            <div data-form-errors class="hidden rounded-xl bg-red-50 px-3 py-2 text-sm text-red-700"></div>
            <div>
                <x-input-label for="qc_payment_method_name" :value="'Nome'" />
                <x-text-input id="qc_payment_method_name" name="name" class="{{ $inputClass }}" required />
                <p data-error-for="name" class="mt-1 hidden text-xs text-red-600"></p>
            </div>
            <div class="flex items-center gap-2 pt-2">
                <button type="submit" class="flux-primary-btn">Salvar</button>
                <button type="button" x-data x-on:click="$dispatch('close-modal', 'qc-payment-method')" class="flux-secondary-btn">Cancelar</button>
            </div>
        </form>
    </x-modal>

    <x-modal name="qc-counterparty" maxWidth="md">
        <form method="POST" action="{{ route('quick-create.counterparties') }}" data-quick-create data-target-select="counterparty_id" data-modal="qc-counterparty" class="space-y-4 p-6">
            @csrf
            <h3 id="counterparty-modal-title" class="font-display text-2xl font-bold text-[var(--flux-ink)]">{{ $counterpartyModalTitle }}</h3>
            <div data-form-errors class="hidden rounded-xl bg-red-50 px-3 py-2 text-sm text-red-700"></div>
            <div>
                <x-input-label id="counterparty-name-label" for="qc_counterparty_name" :value="$counterpartyNameLabel" />
                <x-text-input id="qc_counterparty_name" name="name" class="{{ $inputClass }}" required />
                <p data-error-for="name" class="mt-1 hidden text-xs text-red-600"></p>
            </div>
            <div>
                <x-input-label for="qc_counterparty_type" :value="'Tipo'" />
                <select id="qc_counterparty_type" name="type" class="{{ $inputClass }}" required>
                    <option value="client" @selected($counterpartyDefaultType === 'client')>Cliente</option>
                    <option value="supplier" @selected($counterpartyDefaultType === 'supplier')>Fornecedor</option>
                </select>
                <p data-error-for="type" class="mt-1 hidden text-xs text-red-600"></p>
            </div>
            <div class="flex items-center gap-2 pt-2">
                <button type="submit" class="flux-primary-btn">Salvar</button>
                <button type="button" x-data x-on:click="$dispatch('close-modal', 'qc-counterparty')" class="flux-secondary-btn">Cancelar</button>
            </div>
        </form>
    </x-modal>

    <x-modal name="qc-category" maxWidth="md">
        <form method="POST" action="{{ route('quick-create.categories') }}" data-quick-create data-target-select="category_id" data-modal="qc-category" class="space-y-4 p-6">
            @csrf
            <h3 class="font-display text-2xl font-bold text-[var(--flux-ink)]">Nova categoria</h3>
            <div data-form-errors class="hidden rounded-xl bg-red-50 px-3 py-2 text-sm text-red-700"></div>
            <div>
                <x-input-label for="qc_category_name" :value="'Nome'" />
                <x-text-input id="qc_category_name" name="name" class="{{ $inputClass }}" required />
                <p data-error-for="name" class="mt-1 hidden text-xs text-red-600"></p>
            </div>
            <div id="qc_category_fields_grid" class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <x-input-label for="qc_category_type" :value="'Tipo'" />
                    <select id="qc_category_type" name="type" class="{{ $inputClass }}" data-sync-transaction-type="category" required>
                        <option value="income">Receita</option>
                        <option value="expense">Despesa</option>
                    </select>
                    <p data-error-for="type" class="mt-1 hidden text-xs text-red-600"></p>
                </div>
                <div id="qc_category_cost_type_wrap">
                    <x-input-label for="qc_category_cost_type" :value="'Custo'" />
                    <select id="qc_category_cost_type" name="cost_type" class="{{ $inputClass }}">
                        <option value="fixed">Fixo</option>
                        <option value="variable">Variavel</option>
                        <option value="income" hidden>Receita</option>
                    </select>
                    <p data-error-for="cost_type" class="mt-1 hidden text-xs text-red-600"></p>
                </div>
            </div>
            <div class="flex items-center gap-2 pt-2">
                <button type="submit" class="flux-primary-btn">Salvar</button>
                <button type="button" x-data x-on:click="$dispatch('close-modal', 'qc-category')" class="flux-secondary-btn">Cancelar</button>
            </div>
        </form>
    </x-modal>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            const transactionTypeSelect = document.getElementById('type');
            const paymentStatusSelect = document.getElementById('payment_status');
            const paymentStatusLabel = document.getElementById('payment-status-label');
            const paymentStatusPayableOption = document.getElementById('payment-status-payable-option');
            const paymentStatusPaidOption = document.getElementById('payment-status-paid-option');
            const expenseTypeLabel = document.getElementById('expense-type-label');
            const paymentDateLabel = document.getElementById('payment-date-label');
            const paymentDateInput = document.getElementById('payment_date');
            const categorySelect = document.getElementById('category_id');
            const categoryPlaceholderOption = document.getElementById('category-placeholder-option');
            const counterpartyFieldLabel = document.getElementById('counterparty-field-label');
            const counterpartyCreateTrigger = document.getElementById('counterparty-create-trigger');
            const counterpartyPlaceholderOption = document.getElementById('counterparty-placeholder-option');
            const counterpartySelect = document.getElementById('counterparty_id');
            const counterpartyModalTitle = document.getElementById('counterparty-modal-title');
            const counterpartyNameLabel = document.getElementById('counterparty-name-label');
            const counterpartyTypeSelect = document.getElementById('qc_counterparty_type');
            const quickCategoryTypeSelect = document.getElementById('qc_category_type');
            const quickCategoryFieldsGrid = document.getElementById('qc_category_fields_grid');
            const quickCategoryCostTypeWrap = document.getElementById('qc_category_cost_type_wrap');
            const quickCategoryCostTypeSelect = document.getElementById('qc_category_cost_type');

            const upsertOption = (selectElement, id, label, selected = false) => {
                if (!selectElement) return null;
                const existingOption = [...selectElement.options].find((option) => option.value === String(id));
                if (existingOption) {
                    existingOption.textContent = label;
                    if (selected) selectElement.value = String(id);
                    return existingOption;
                }
                const option = new Option(label, String(id), selected, selected);
                selectElement.add(option);
                if (selected) selectElement.value = String(id);
                return option;
            };

            const clearErrors = (form) => {
                const genericErrors = form.querySelector('[data-form-errors]');
                if (genericErrors) {
                    genericErrors.classList.add('hidden');
                    genericErrors.textContent = '';
                }
                form.querySelectorAll('[data-error-for]').forEach((node) => {
                    node.classList.add('hidden');
                    node.textContent = '';
                });
            };

            const showErrors = (form, errors, genericMessage = null) => {
                const genericErrors = form.querySelector('[data-form-errors]');
                if (genericErrors && genericMessage) {
                    genericErrors.classList.remove('hidden');
                    genericErrors.textContent = genericMessage;
                }
                if (!errors || typeof errors !== 'object') return;
                Object.entries(errors).forEach(([field, messages]) => {
                    const target = form.querySelector(`[data-error-for="${field}"]`);
                    if (!target) return;
                    target.classList.remove('hidden');
                    target.textContent = Array.isArray(messages) ? messages[0] : messages;
                });
            };

            const syncTypeDefaults = () => {
                const transactionType = transactionTypeSelect?.value;
                document.querySelectorAll('[data-sync-transaction-type]').forEach((element) => {
                    if (element.dataset.syncedByUser === '1') return;
                    if (transactionType) element.value = transactionType;
                });
                syncPaymentStatusContext();
                syncCategoryContext();
                syncCounterpartyContext();
                syncQuickCategoryCostTypeVisibility();
            };

            const syncPaymentStatusContext = () => {
                const transactionType = transactionTypeSelect?.value;
                const isIncome = transactionType === 'income';
                const payableLabel = isIncome ? 'A receber' : 'A pagar';
                const paidLabel = isIncome ? 'Recebido' : 'Pago';
                const statusLabel = isIncome ? 'Status de recebimento' : 'Status de pagamento';
                const typeLabel = isIncome
                    ? 'Tipo de receita pessoal/profissional'
                    : 'Tipo de despesa pessoal/profissional';
                const dateLabel = isIncome ? 'Data de recebimento' : 'Data de pagamento';
                const isPayable = paymentStatusSelect?.value === 'payable';

                if (paymentStatusLabel) paymentStatusLabel.textContent = statusLabel;
                if (paymentStatusPayableOption) paymentStatusPayableOption.textContent = payableLabel;
                if (paymentStatusPaidOption) paymentStatusPaidOption.textContent = paidLabel;
                if (expenseTypeLabel) expenseTypeLabel.textContent = typeLabel;
                if (paymentDateLabel) paymentDateLabel.textContent = dateLabel;

                if (paymentDateInput) {
                    paymentDateInput.readOnly = isPayable;
                    paymentDateInput.required = !isPayable;
                    paymentDateInput.classList.toggle('bg-gray-50', isPayable);
                    paymentDateInput.classList.toggle('cursor-not-allowed', isPayable);
                }
            };

            const syncCategoryContext = () => {
                const transactionType = transactionTypeSelect?.value;
                const expectedCategoryType = transactionType === 'income'
                    ? 'income'
                    : (transactionType === 'expense' ? 'expense' : null);

                if (categoryPlaceholderOption) {
                    categoryPlaceholderOption.textContent = transactionType === 'income'
                        ? 'Selecione categoria de receita'
                        : (transactionType === 'expense' ? 'Selecione categoria de despesa' : 'Selecione');
                }

                if (!categorySelect) {
                    return;
                }

                [...categorySelect.options].forEach((option) => {
                    if (!option.value) {
                        return;
                    }

                    const optionType = option.dataset.categoryType || null;
                    const isVisible = expectedCategoryType ? optionType === expectedCategoryType : true;
                    option.hidden = !isVisible;
                    option.disabled = !isVisible;
                });

                const selectedOption = categorySelect.options[categorySelect.selectedIndex];
                if (selectedOption && selectedOption.value && selectedOption.disabled) {
                    categorySelect.value = '';
                }
            };

            const syncCounterpartyContext = () => {
                const transactionType = transactionTypeSelect?.value;
                const isIncome = transactionType === 'income';
                const isExpense = transactionType === 'expense';

                const fieldLabel = isIncome ? 'Cliente' : (isExpense ? 'Fornecedor' : 'Contraparte');
                const actionLabel = isIncome ? '+ Criar cliente' : (isExpense ? '+ Criar fornecedor' : '+ Criar contraparte');
                const placeholderLabel = isIncome ? 'Selecione o cliente' : (isExpense ? 'Selecione o fornecedor' : 'Selecione');
                const modalTitle = isIncome ? 'Novo cliente' : (isExpense ? 'Novo fornecedor' : 'Nova contraparte');
                const nameLabel = isIncome ? 'Nome do cliente' : (isExpense ? 'Nome do fornecedor' : 'Nome');
                const expectedCounterpartyType = isIncome ? 'client' : (isExpense ? 'supplier' : null);

                if (counterpartyFieldLabel) counterpartyFieldLabel.textContent = fieldLabel;
                if (counterpartyCreateTrigger) counterpartyCreateTrigger.textContent = actionLabel;
                if (counterpartyPlaceholderOption) counterpartyPlaceholderOption.textContent = placeholderLabel;
                if (counterpartyModalTitle) counterpartyModalTitle.textContent = modalTitle;
                if (counterpartyNameLabel) counterpartyNameLabel.textContent = nameLabel;

                if (counterpartyTypeSelect) {
                    counterpartyTypeSelect.value = expectedCounterpartyType ?? 'supplier';
                }

                if (counterpartySelect) {
                    [...counterpartySelect.options].forEach((option) => {
                        if (!option.value) {
                            return;
                        }

                        const optionType = option.dataset.counterpartyType || null;
                        const isVisible = expectedCounterpartyType ? optionType === expectedCounterpartyType : true;
                        option.hidden = !isVisible;
                        option.disabled = !isVisible;
                    });

                    const selectedOption = counterpartySelect.options[counterpartySelect.selectedIndex];
                    if (selectedOption && selectedOption.value && selectedOption.disabled) {
                        counterpartySelect.value = '';
                    }
                }
            };

            const syncQuickCategoryCostTypeVisibility = () => {
                if (!quickCategoryTypeSelect || !quickCategoryCostTypeWrap || !quickCategoryCostTypeSelect) {
                    return;
                }

                const isExpense = quickCategoryTypeSelect.value === 'expense';
                quickCategoryCostTypeWrap.classList.toggle('hidden', !isExpense);

                if (quickCategoryFieldsGrid) {
                    quickCategoryFieldsGrid.classList.toggle('sm:grid-cols-2', isExpense);
                    quickCategoryFieldsGrid.classList.toggle('sm:grid-cols-1', !isExpense);
                }

                quickCategoryCostTypeSelect.required = isExpense;
                quickCategoryCostTypeSelect.disabled = !isExpense;

                if (!isExpense) {
                    quickCategoryCostTypeSelect.value = 'income';
                } else if (!['fixed', 'variable'].includes(quickCategoryCostTypeSelect.value)) {
                    quickCategoryCostTypeSelect.value = 'fixed';
                }
            };

            document.querySelectorAll('[data-sync-transaction-type]').forEach((element) => {
                element.addEventListener('change', () => {
                    element.dataset.syncedByUser = '1';
                });
            });

            if (transactionTypeSelect) {
                transactionTypeSelect.addEventListener('change', syncTypeDefaults);
                syncTypeDefaults();
            }

            if (paymentStatusSelect) {
                paymentStatusSelect.addEventListener('change', syncPaymentStatusContext);
                syncPaymentStatusContext();
            }

            if (quickCategoryTypeSelect) {
                quickCategoryTypeSelect.addEventListener('change', syncQuickCategoryCostTypeVisibility);
            }
            syncCounterpartyContext();
            syncQuickCategoryCostTypeVisibility();

            document.querySelectorAll('form[data-quick-create]').forEach((form) => {
                form.addEventListener('submit', async (event) => {
                    event.preventDefault();
                    clearErrors(form);

                    const submitButton = form.querySelector('button[type="submit"]');
                    const originalText = submitButton ? submitButton.textContent : null;
                    if (submitButton) {
                        submitButton.disabled = true;
                        submitButton.textContent = 'Salvando...';
                    }

                    try {
                        const response = await fetch(form.action, {
                            method: 'POST',
                            headers: {
                                Accept: 'application/json',
                                'X-Requested-With': 'XMLHttpRequest',
                                'X-CSRF-TOKEN': csrfToken || '',
                            },
                            body: new FormData(form),
                        });

                        const payload = await response.json().catch(() => ({}));
                        if (!response.ok) {
                            if (response.status === 422) {
                                showErrors(form, payload.errors, 'Confira os campos e tente novamente.');
                            } else {
                                showErrors(form, null, 'Nao foi possivel salvar agora.');
                            }
                            return;
                        }

                        const targetSelectId = form.dataset.targetSelect;
                        const targetSelect = targetSelectId ? document.getElementById(targetSelectId) : null;

                        if (targetSelect) {
                            const option = upsertOption(targetSelect, payload.id, payload.label, true);

                            if (targetSelectId === 'counterparty_id' && option && payload.counterparty_type) {
                                option.dataset.counterpartyType = payload.counterparty_type;
                            }

                            if (targetSelectId === 'category_id' && option && payload.category_type) {
                                option.dataset.categoryType = payload.category_type;
                            }
                        }

                        form.reset();
                        syncTypeDefaults();
                        syncQuickCategoryCostTypeVisibility();

                        if (form.dataset.modal) {
                            window.dispatchEvent(new CustomEvent('close-modal', { detail: form.dataset.modal }));
                        }
                    } catch (error) {
                        showErrors(form, null, 'Erro de conexao ao tentar salvar.');
                    } finally {
                        if (submitButton) {
                            submitButton.disabled = false;
                            submitButton.textContent = originalText || 'Salvar';
                        }
                    }
                });
            });
        });
    </script>
</x-app-layout>
