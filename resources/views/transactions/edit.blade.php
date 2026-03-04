<x-app-layout>
    @php
        $canSubmit = $bankAccounts->isNotEmpty()
            && $paymentMethods->isNotEmpty()
            && $counterparties->isNotEmpty()
            && $categories->isNotEmpty();

        $transactionType = old('type', $transaction->type);
        $expectedPaymentDate = old('expected_payment_date', $transaction->expected_payment_date?->format('Y-m-d'));
        $paymentDate = old('payment_date', $transaction->payment_date?->format('Y-m-d'));
        $currentPaymentStatus = old('payment_status', $transaction->payment_status);
        $transactionTypeLabel = $transactionType === 'income' ? 'Receita' : 'Despesa';
        $isIncome = $transactionType === 'income';
        $counterpartyLabel = $isIncome ? 'Cliente' : 'Fornecedor';
        $counterpartyActionLabel = $isIncome ? '+ Criar cliente' : '+ Criar fornecedor';
        $counterpartyPlaceholder = $isIncome ? 'Selecione o cliente' : 'Selecione o fornecedor';
        $counterpartyModalTitle = $isIncome ? 'Novo cliente' : 'Novo fornecedor';
        $counterpartyNameLabel = $isIncome ? 'Nome do cliente' : 'Nome do fornecedor';
        $counterpartyDefaultType = $isIncome ? 'client' : 'supplier';
        $paymentStatusLabel = $isIncome ? 'Status de recebimento' : 'Status de pagamento';
        $payableLabel = $isIncome ? 'A receber' : 'A pagar';
        $paidLabel = $isIncome ? 'Recebido' : 'Pago';
        $expenseTypeLabel = $isIncome
            ? 'Tipo de receita pessoal/profissional'
            : 'Tipo de despesa pessoal/profissional';
        $paymentDateLabel = $isIncome ? 'Data de recebimento' : 'Data de pagamento';
        $showPaymentDateField = $currentPaymentStatus === 'paid';
        $inputClass = 'mt-1 block w-full rounded-xl border-[var(--flux-border)] bg-white/90 shadow-sm focus:border-[#17736a] focus:ring-[#17736a]';
        $linkClass = 'text-xs font-semibold text-[var(--flux-muted)] transition hover:text-[var(--flux-ink)]';
        $categoryPlaceholder = $isIncome ? 'Selecione categoria de receita' : 'Selecione categoria de despesa';
    @endphp

    <div class="py-8">
        <div class="flux-shell space-y-6">
            <div>
                <h1 class="font-display text-4xl font-bold text-[var(--flux-ink)]">Editar transacao</h1>
                <p class="mt-1 text-lg text-[var(--flux-muted)]">Atualize dados operacionais da transacao mantendo a estrutura original.</p>
            </div>

            <section class="flux-card overflow-hidden">
                <div class="border-b border-[var(--flux-border)] bg-[#f4f6f1] px-6 py-4 text-sm text-[var(--flux-muted)]">
                    Parcela e tipo da transacao sao somente leitura.
                </div>

                <form method="POST" action="{{ route('transactions.update', $transaction->id) }}" class="space-y-5 p-6">
                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div>
                            <x-input-label :value="'Organizacao'" />
                            <p class="mt-1 rounded-xl border border-[var(--flux-border)] bg-[#f8f9f5] px-3 py-2 text-sm font-medium text-[var(--flux-ink)]">
                                {{ $transaction->organization->name ?? '-' }}
                            </p>
                        </div>
                        <div>
                            <x-input-label :value="'Tipo da transacao'" />
                            <p class="mt-1 rounded-xl border border-[var(--flux-border)] bg-[#f8f9f5] px-3 py-2 text-sm font-medium text-[var(--flux-ink)]">
                                {{ $transactionTypeLabel }}
                            </p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                        <div>
                            <x-input-label id="payment-status-label" for="payment_status" :value="$paymentStatusLabel" />
                            <select id="payment_status" name="payment_status" class="{{ $inputClass }}" required>
                                <option value="">Selecione</option>
                                <option id="payment-status-payable-option" value="payable" @selected($currentPaymentStatus === 'payable')>{{ $payableLabel }}</option>
                                <option id="payment-status-paid-option" value="paid" @selected($currentPaymentStatus === 'paid')>{{ $paidLabel }}</option>
                            </select>
                            <x-input-error :messages="$errors->get('payment_status')" class="mt-2" />
                        </div>
                        <div>
                            <x-input-label id="expense-type-label" for="expense_type" :value="$expenseTypeLabel" />
                            <select id="expense_type" name="expense_type" class="{{ $inputClass }}" required>
                                <option value="">Selecione</option>
                                <option value="professional" @selected(old('expense_type', $transaction->expense_type) === 'professional')>Profissional</option>
                                <option value="personal" @selected(old('expense_type', $transaction->expense_type) === 'personal')>Pessoal</option>
                            </select>
                            <x-input-error :messages="$errors->get('expense_type')" class="mt-2" />
                        </div>
                        <div>
                            <x-input-label :value="'Numero da parcela'" />
                            <p class="mt-1 rounded-xl border border-[var(--flux-border)] bg-[#f8f9f5] px-3 py-2 text-sm font-medium text-[var(--flux-ink)]">
                                {{ $transaction->installment_number }}
                            </p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                        <div>
                            <div class="flex items-center justify-between gap-2">
                                <x-input-label for="category_id" :value="'Categoria'" />
                                <button type="button" x-data x-on:click="$dispatch('open-modal', 'qc-category')" class="{{ $linkClass }}">+ Criar categoria</button>
                            </div>
                            <select id="category_id" name="category_id" class="{{ $inputClass }}" required>
                                <option id="category-placeholder-option" value="">{{ $categoryPlaceholder }}</option>
                                @foreach ($categories as $category)
                                    <option data-category-type="{{ $category->type }}" value="{{ $category->id }}" @selected(old('category_id', $transaction->category_id) == $category->id)>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('category_id')" class="mt-2" />
                        </div>
                        <div>
                            <x-input-label for="amount" :value="'Valor (total)'" />
                            <x-text-input id="amount" name="amount" type="number" step="0.01" min="0" class="{{ $inputClass }}" :value="old('amount', $transaction->amount)" required />
                            <x-input-error :messages="$errors->get('amount')" class="mt-2" />
                        </div>
                        <div class="hidden md:block"></div>
                    </div>

                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div id="payment-date-field-wrap">
                            <x-input-label id="payment-date-label" for="payment_date" :value="$paymentDateLabel" />
                            <input id="payment_date" name="payment_date" type="date"
                                class="{{ $inputClass }} {{ ! $showPaymentDateField ? 'bg-gray-50 cursor-not-allowed' : '' }}"
                                value="{{ $paymentDate }}"
                                @required($showPaymentDateField)
                                @readonly(! $showPaymentDateField)>
                            <x-input-error :messages="$errors->get('payment_date')" class="mt-2" />
                        </div>
                        <div>
                            <x-input-label for="expected_payment_date" :value="'Data esperada'" />
                            <x-text-input id="expected_payment_date" name="expected_payment_date" type="date" class="{{ $inputClass }}" :value="$expectedPaymentDate" required />
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
                                    <option value="{{ $bankAccount->id }}" @selected(old('bank_account_id', $transaction->bank_account_id) == $bankAccount->id)>{{ $bankAccount->name }} - {{ $bankAccount->organization->name ?? '-' }}</option>
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
                                    <option value="{{ $paymentMethod->id }}" @selected(old('payment_method_id', $transaction->payment_method_id) == $paymentMethod->id)>{{ $paymentMethod->name }}</option>
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
                                <option data-counterparty-type="{{ $counterparty->type }}" value="{{ $counterparty->id }}" @selected(old('counterparty_id', $transaction->counterparty_id) == $counterparty->id)>{{ $counterparty->name }} ({{ $counterparty->type_label }}) - {{ $counterparty->organization->name ?? '-' }}</option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('counterparty_id')" class="mt-2" />
                    </div>

                    <div class="flex flex-wrap items-center gap-3 pt-2">
                        <button type="submit" class="flux-primary-btn" @disabled(! $canSubmit)>Atualizar transacao</button>
                        <a href="{{ route('transactions.index', ['type' => $transaction->type]) }}" class="flux-secondary-btn">Voltar</a>
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
                    <select id="qc_category_type" name="type" class="{{ $inputClass }}" required>
                        <option value="income" @selected($isIncome)>Receita</option>
                        <option value="expense" @selected(! $isIncome)>Despesa</option>
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
            const fixedTransactionType = @js($transactionType);
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
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

            const syncPaymentStatusContext = () => {
                const isIncome = fixedTransactionType === 'income';
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
                const expectedCategoryType = fixedTransactionType === 'income' ? 'income' : 'expense';

                if (categoryPlaceholderOption) {
                    categoryPlaceholderOption.textContent = fixedTransactionType === 'income'
                        ? 'Selecione categoria de receita'
                        : 'Selecione categoria de despesa';
                }

                if (categorySelect) {
                    [...categorySelect.options].forEach((option) => {
                        if (!option.value) {
                            return;
                        }

                        const optionType = option.dataset.categoryType || null;
                        const isVisible = optionType === expectedCategoryType;
                        option.hidden = !isVisible;
                        option.disabled = !isVisible;
                    });

                    const selectedOption = categorySelect.options[categorySelect.selectedIndex];
                    if (selectedOption && selectedOption.value && selectedOption.disabled) {
                        categorySelect.value = '';
                    }
                }

                if (quickCategoryTypeSelect) {
                    quickCategoryTypeSelect.value = expectedCategoryType;
                    [...quickCategoryTypeSelect.options].forEach((option) => {
                        const isVisible = option.value === expectedCategoryType;
                        option.hidden = !isVisible;
                        option.disabled = !isVisible;
                    });
                }
            };

            const syncCounterpartyContext = () => {
                const isIncome = fixedTransactionType === 'income';
                const fieldLabel = isIncome ? 'Cliente' : 'Fornecedor';
                const actionLabel = isIncome ? '+ Criar cliente' : '+ Criar fornecedor';
                const placeholderLabel = isIncome ? 'Selecione o cliente' : 'Selecione o fornecedor';
                const modalTitle = isIncome ? 'Novo cliente' : 'Novo fornecedor';
                const nameLabel = isIncome ? 'Nome do cliente' : 'Nome do fornecedor';
                const expectedCounterpartyType = isIncome ? 'client' : 'supplier';

                if (counterpartyFieldLabel) counterpartyFieldLabel.textContent = fieldLabel;
                if (counterpartyCreateTrigger) counterpartyCreateTrigger.textContent = actionLabel;
                if (counterpartyPlaceholderOption) counterpartyPlaceholderOption.textContent = placeholderLabel;
                if (counterpartyModalTitle) counterpartyModalTitle.textContent = modalTitle;
                if (counterpartyNameLabel) counterpartyNameLabel.textContent = nameLabel;

                if (counterpartyTypeSelect) {
                    counterpartyTypeSelect.value = expectedCounterpartyType;
                    [...counterpartyTypeSelect.options].forEach((option) => {
                        const isVisible = option.value === expectedCounterpartyType;
                        option.hidden = !isVisible;
                        option.disabled = !isVisible;
                    });
                }

                if (counterpartySelect) {
                    [...counterpartySelect.options].forEach((option) => {
                        if (!option.value) {
                            return;
                        }

                        const optionType = option.dataset.counterpartyType || null;
                        const isVisible = optionType === expectedCounterpartyType;
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

            if (paymentStatusSelect) {
                paymentStatusSelect.addEventListener('change', syncPaymentStatusContext);
            }

            syncPaymentStatusContext();
            syncCategoryContext();
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
                        syncCategoryContext();
                        syncCounterpartyContext();
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
