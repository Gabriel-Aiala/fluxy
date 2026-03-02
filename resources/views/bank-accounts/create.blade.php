<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Nova Conta Bancaria') }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    @if ($organizations->isEmpty())
                        <div class="mb-4 rounded-md bg-yellow-100 text-yellow-800 px-4 py-2">
                            Nenhuma organizacao encontrada. Cadastre uma organizacao antes de criar conta bancaria.
                        </div>
                    @endif

                    <form method="POST" action="{{ route('bank-accounts.store') }}" class="space-y-4">
                        @csrf

                        <div>
                            <x-input-label for="organization_id" :value="__('Organizacao')" />
                            <select id="organization_id" name="organization_id"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                required>
                                <option value="">Selecione...</option>
                                @foreach ($organizations as $organization)
                                    <option value="{{ $organization->id }}"
                                        @selected(old('organization_id') == $organization->id)>
                                        {{ $organization->name }}
                                    </option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('organization_id')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="name" :value="__('Nome')" />
                            <x-text-input id="name" name="name" type="text" class="mt-1 block w-full"
                                :value="old('name')" required />
                            <x-input-error :messages="$errors->get('name')" class="mt-2" />
                        </div>

                        <div class="flex items-center gap-3">
                            <a href="{{ route('bank-accounts.index') }}"
                                class="inline-flex items-center px-4 py-2 bg-gray-200 border border-transparent rounded-md font-semibold text-xs uppercase tracking-widest text-gray-700 hover:bg-gray-300">
                                Cancelar
                            </a>
                            <x-primary-button :disabled="$organizations->isEmpty()">
                                Salvar
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
