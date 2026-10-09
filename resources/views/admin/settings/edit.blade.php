<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="font-semibold text-xl text-gray-800">System Settings</h2>
            <p class="mt-1 text-sm text-gray-500">Company information and document numbering.</p>
        </div>
    </x-slot>

    <div class="max-w-4xl">
        <form method="POST" action="{{ route('admin.settings.update') }}" class="space-y-8">
            @csrf
            @method('PUT')

            {{-- General settings --}}
            <div class="bg-white shadow-sm rounded-lg overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                    <h3 class="font-semibold text-gray-800">General</h3>
                    <p class="text-sm text-gray-500">Company details shown on documents and reports.</p>
                </div>
                <div class="p-6 space-y-4">
                    <div>
                        <x-input-label for="company_name" value="Company Name" />
                        <x-text-input id="company_name" name="settings[company_name]" type="text" class="mt-1 block w-full"
                            :value="old('settings.company_name', \App\Models\Setting::getValue('company_name'))" required />
                        <x-input-error :messages="$errors->get('settings.company_name')" class="mt-2" />
                    </div>
                    <div>
                        <x-input-label for="company_phone" value="Company Phone" />
                        <x-text-input id="company_phone" name="settings[company_phone]" type="text" class="mt-1 block w-full"
                            :value="old('settings.company_phone', \App\Models\Setting::getValue('company_phone'))" />
                        <x-input-error :messages="$errors->get('settings.company_phone')" class="mt-2" />
                    </div>
                    <div>
                        <x-input-label for="company_address" value="Company Address" />
                        <textarea id="company_address" name="settings[company_address]" rows="3"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-eucalyptus focus:ring-brand-eucalyptus">{{ old('settings.company_address', \App\Models\Setting::getValue('company_address')) }}</textarea>
                        <x-input-error :messages="$errors->get('settings.company_address')" class="mt-2" />
                    </div>
                </div>
            </div>

            {{-- Document numbering --}}
            <div class="bg-white shadow-sm rounded-lg overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                    <h3 class="font-semibold text-gray-800">Document Numbering</h3>
                    <p class="text-sm text-gray-500">Prefixes and next numbers for auto-generated document codes.</p>
                </div>
                <div class="p-6 space-y-6">
                    @php
                        $numberingGroups = [
                            ['label' => 'Sales Invoices', 'group' => 'sales', 'prefix' => 'invoice_prefix', 'next' => 'invoice_next'],
                            ['label' => 'Stock Receiving (GRN)', 'group' => 'stock_receiving', 'prefix' => 'receipt_prefix', 'next' => 'receipt_next'],
                            ['label' => 'Customer Payments', 'group' => 'payments', 'prefix' => 'receipt_prefix', 'next' => 'receipt_next'],
                            ['label' => 'Sale Returns', 'group' => 'sale_returns', 'prefix' => 'return_prefix', 'next' => 'return_next'],
                            ['label' => 'Stock Adjustments', 'group' => 'stock_adjustments', 'prefix' => 'adjustment_prefix', 'next' => 'adjustment_next'],
                        ];
                    @endphp

                    @foreach ($numberingGroups as $group)
                        @php
                            $prefixKey = $group['group'] . '.' . $group['prefix'];
                            $nextKey = $group['group'] . '.' . $group['next'];
                        @endphp
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <x-input-label :value="$group['label'] . ' Prefix'" />
                                <x-text-input
                                    name="settings[{{ $group['group'] }}][{{ $group['prefix'] }}]"
                                    type="text"
                                    class="mt-1 block w-full"
                                    :value="old('settings.' . $prefixKey, \App\Models\Setting::getValue($prefixKey))"
                                    required
                                />
                                <x-input-error :messages="$errors->get('settings.' . $prefixKey)" class="mt-2" />
                            </div>
                            <div>
                                <x-input-label :value="$group['label'] . ' Next Number'" />
                                <x-text-input
                                    name="settings[{{ $group['group'] }}][{{ $group['next'] }}]"
                                    type="number"
                                    min="1"
                                    class="mt-1 block w-full"
                                    :value="old('settings.' . $nextKey, \App\Models\Setting::getValue($nextKey))"
                                    required
                                />
                                <x-input-error :messages="$errors->get('settings.' . $nextKey)" class="mt-2" />
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="flex gap-3">
                <x-primary-button>Save Settings</x-primary-button>
                <a href="{{ route('dashboard') }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md text-sm text-gray-700 hover:bg-gray-50">Cancel</a>
            </div>
        </form>
    </div>
</x-app-layout>
