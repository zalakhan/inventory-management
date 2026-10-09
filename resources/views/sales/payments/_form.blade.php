<div class="bg-white shadow-sm rounded-lg p-6 space-y-6">
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
            <x-input-label for="customer_id" value="Customer" />
            <select id="customer_id" name="customer_id" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-eucalyptus focus:ring-brand-eucalyptus">
                <option value="">— Select customer —</option>
                @foreach ($customers as $customer)
                    <option value="{{ $customer->id }}" @selected(old('customer_id', $payment?->customer_id) == $customer->id)>
                        {{ $customer->code }} — {{ $customer->name }}
                    </option>
                @endforeach
            </select>
            <x-input-error :messages="$errors->get('customer_id')" class="mt-1" />
        </div>

        <div>
            <x-input-label for="payment_date" value="Payment Date" />
            <x-text-input id="payment_date" name="payment_date" type="date" class="mt-1 block w-full"
                :value="old('payment_date', $payment?->payment_date?->format('Y-m-d') ?? now()->format('Y-m-d'))" required />
            <x-input-error :messages="$errors->get('payment_date')" class="mt-1" />
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div>
            <x-input-label for="amount" value="Amount" />
            <x-text-input id="amount" name="amount" type="number" step="0.01" min="0.01" class="mt-1 block w-full"
                :value="old('amount', $payment?->amount)" required />
            <x-input-error :messages="$errors->get('amount')" class="mt-1" />
        </div>

        <div>
            <x-input-label for="payment_method" value="Payment Method" />
            <select id="payment_method" name="payment_method" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-eucalyptus focus:ring-brand-eucalyptus">
                @foreach (['cash' => 'Cash', 'bank_transfer' => 'Bank Transfer', 'cheque' => 'Cheque', 'other' => 'Other'] as $value => $label)
                    <option value="{{ $value }}" @selected(old('payment_method', $payment?->payment_method ?? 'cash') === $value)>{{ $label }}</option>
                @endforeach
            </select>
            <x-input-error :messages="$errors->get('payment_method')" class="mt-1" />
        </div>

        <div>
            <x-input-label for="reference_no" value="Reference No." />
            <x-text-input id="reference_no" name="reference_no" type="text" class="mt-1 block w-full"
                :value="old('reference_no', $payment?->reference_no)" placeholder="Cheque / txn ref" />
            <x-input-error :messages="$errors->get('reference_no')" class="mt-1" />
        </div>
    </div>

    <div>
        <x-input-label for="notes" value="Notes" />
        <textarea id="notes" name="notes" rows="2" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-eucalyptus focus:ring-brand-eucalyptus">{{ old('notes', $payment?->notes) }}</textarea>
        <x-input-error :messages="$errors->get('notes')" class="mt-1" />
    </div>
</div>
