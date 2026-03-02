<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Editar Transacao') }}
        </h2>
    </x-slot>

    @php
        $canSubmit = $organizations->isNotEmpty()
            && $transactionGroups->isNotEmpty()
            && $bankAccounts->isNotEmpty()
            && $paymentMethods->isNotEmpty()
            && $counterparties->isNotEmpty()
            && $categories->isNotEmpty();

        $expectedPaymentDate = $transaction->expected_payment_date
            ? $transaction->expected_payment_date->format('Y-m-d')
            : null;
        $paymentDate = $transaction->payment_date
            ? $transaction->payment_date->format('Y-m-d')
            : null;
    @endphp

    <div class="py-8">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <form method="POST" action="{{ route('transactions.update', $transaction->id) }}" class="space-y-4">
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
                                        @selected(old('organization_id', $transaction->organization_id) == $organization->id)>
                                        {{ $organization->name }}
                                    </option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('organization_id')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="transaction_group_id" :value="__('Grupo de Transacao')" />
                            <select id="transaction_group_id" name="transaction_group_id"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                required>
                                <option value="">Selecione...</option>
                                @foreach ($transactionGroups as $transactionGroup)
                                    <option value="{{ $transactionGroup->id }}"
                                        @selected(old('transaction_group_id', $transaction->transaction_group_id) == $transactionGroup->id)>
                                        #{{ $transactionGroup->id }} - {{ $transactionGroup->type }} - {{ $transactionGroup->organization->name ?? '-' }}
                                    </option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('transaction_group_id')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="bank_account_id" :value="__('Conta Bancaria')" />
                            <select id="bank_account_id" name="bank_account_id"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                required>
                                <option value="">Selecione...</option>
                                @foreach ($bankAccounts as $bankAccount)
                                    <option value="{{ $bankAccount->id }}"
                                        @selected(old('bank_account_id', $transaction->bank_account_id) == $bankAccount->id)>
                                        {{ $bankAccount->name }} - {{ $bankAccount->organization->name ?? '-' }}
                                    </option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('bank_account_id')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="payment_method_id" :value="__('Metodo de Pagamento')" />
                            <select id="payment_method_id" name="payment_method_id"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                required>
                                <option value="">Selecione...</option>
                                @foreach ($paymentMethods as $paymentMethod)
                                    <option value="{{ $paymentMethod->id }}"
                                        @selected(old('payment_method_id', $transaction->payment_method_id) == $paymentMethod->id)>
                                        {{ $paymentMethod->name }}
                                    </option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('payment_method_id')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="counterparty_id" :value="__('Contraparte')" />
                            <select id="counterparty_id" name="counterparty_id"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                required>
                                <option value="">Selecione...</option>
                                @foreach ($counterparties as $counterparty)
                                    <option value="{{ $counterparty->id }}"
                                        @selected(old('counterparty_id', $transaction->counterparty_id) == $counterparty->id)>
                                        {{ $counterparty->name }} ({{ $counterparty->type }}) - {{ $counterparty->organization->name ?? '-' }}
                                    </option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('counterparty_id')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="category_id" :value="__('Categoria')" />
                            <select id="category_id" name="category_id"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                required>
                                <option value="">Selecione...</option>
                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}"
                                        @selected(old('category_id', $transaction->category_id) == $category->id)>
                                        {{ $category->name }} ({{ $category->type === 'expense' ? $category->type.'/'.$category->cost_type : $category->type }}) - {{ $category->organization->name ?? '-' }}
                                    </option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('category_id')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="installment_number" :value="__('Numero da Parcela')" />
                            <x-text-input id="installment_number" name="installment_number" type="number" min="1"
                                class="mt-1 block w-full" :value="old('installment_number', $transaction->installment_number)" required />
                            <x-input-error :messages="$errors->get('installment_number')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="expected_payment_date" :value="__('Data Prevista')" />
                            <x-text-input id="expected_payment_date" name="expected_payment_date" type="date"
                                class="mt-1 block w-full" :value="old('expected_payment_date', $expectedPaymentDate)" required />
                            <x-input-error :messages="$errors->get('expected_payment_date')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="payment_date" :value="__('Data de Pagamento')" />
                            <x-text-input id="payment_date" name="payment_date" type="date"
                                class="mt-1 block w-full" :value="old('payment_date', $paymentDate)" required />
                            <x-input-error :messages="$errors->get('payment_date')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="amount" :value="__('Valor')" />
                            <x-text-input id="amount" name="amount" type="number" step="0.01" min="0"
                                class="mt-1 block w-full" :value="old('amount', $transaction->amount)" required />
                            <x-input-error :messages="$errors->get('amount')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="payment_status" :value="__('Status de Pagamento')" />
                            <select id="payment_status" name="payment_status"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                required>
                                <option value="">Selecione...</option>
                                <option value="paid" @selected(old('payment_status', $transaction->payment_status) === 'paid')>paid</option>
                                <option value="payable" @selected(old('payment_status', $transaction->payment_status) === 'payable')>payable</option>
                            </select>
                            <x-input-error :messages="$errors->get('payment_status')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="expense_type" :value="__('Tipo de Gasto')" />
                            <select id="expense_type" name="expense_type"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                required>
                                <option value="">Selecione...</option>
                                <option value="professional" @selected(old('expense_type', $transaction->expense_type) === 'professional')>professional</option>
                                <option value="personal" @selected(old('expense_type', $transaction->expense_type) === 'personal')>personal</option>
                            </select>
                            <x-input-error :messages="$errors->get('expense_type')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="type" :value="__('Tipo da Transacao')" />
                            <select id="type" name="type"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                required>
                                <option value="">Selecione...</option>
                                <option value="income" @selected(old('type', $transaction->type) === 'income')>income</option>
                                <option value="expense" @selected(old('type', $transaction->type) === 'expense')>expense</option>
                            </select>
                            <x-input-error :messages="$errors->get('type')" class="mt-2" />
                        </div>

                        <div class="flex items-center gap-3">
                            <a href="{{ route('transactions.index') }}"
                                class="inline-flex items-center px-4 py-2 bg-gray-200 border border-transparent rounded-md font-semibold text-xs uppercase tracking-widest text-gray-700 hover:bg-gray-300">
                                Cancelar
                            </a>
                            <x-primary-button :disabled="! $canSubmit">
                                Atualizar
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
