<div class="bg-white shadow-sm rounded-lg p-6 space-y-4">
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
            <x-input-label for="code" value="Customer Code" />
            <x-text-input id="code" name="code" type="text" class="mt-1 block w-full" :value="old('code', $customer?->code)" required />
            <x-input-error :messages="$errors->get('code')" class="mt-1" />
        </div>
        <div>
            <x-input-label for="name" value="Name" />
            <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $customer?->name)" required />
            <x-input-error :messages="$errors->get('name')" class="mt-1" />
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
            <x-input-label for="phone" value="Phone" />
            <x-text-input id="phone" name="phone" type="text" class="mt-1 block w-full" :value="old('phone', $customer?->phone)" />
            <x-input-error :messages="$errors->get('phone')" class="mt-1" />
        </div>
        <div>
            <x-input-label for="email" value="Email" />
            <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email', $customer?->email)" />
            <x-input-error :messages="$errors->get('email')" class="mt-1" />
        </div>
    </div>

    <div>
        <x-input-label for="address" value="Address" />
        <textarea id="address" name="address" rows="2" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-eucalyptus focus:ring-brand-eucalyptus">{{ old('address', $customer?->address) }}</textarea>
        <x-input-error :messages="$errors->get('address')" class="mt-1" />
    </div>

    <div>
        <x-input-label for="area_id" value="Area" />
        <select id="area_id" name="area_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-eucalyptus focus:ring-brand-eucalyptus">
            <option value="">— None —</option>
            @foreach ($areas as $area)
                <option value="{{ $area->id }}" @selected(old('area_id', $customer?->area_id) == $area->id)>
                    {{ $area->name }}
                </option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('area_id')" class="mt-1" />
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
            <x-input-label for="opening_balance" value="Opening Balance" />
            <x-text-input id="opening_balance" name="opening_balance" type="number" step="0.01" class="mt-1 block w-full" :value="old('opening_balance', $customer?->opening_balance ?? 0)" />
            <x-input-error :messages="$errors->get('opening_balance')" class="mt-1" />
        </div>
        <div>
            <x-input-label for="credit_limit" value="Credit Limit (optional)" />
            <x-text-input id="credit_limit" name="credit_limit" type="number" step="0.01" min="0" class="mt-1 block w-full" :value="old('credit_limit', $customer?->credit_limit)" />
            <x-input-error :messages="$errors->get('credit_limit')" class="mt-1" />
        </div>
    </div>

    <div class="flex items-center gap-2">
        <input type="hidden" name="is_active" value="0">
        <input id="is_active" name="is_active" type="checkbox" value="1" @checked(old('is_active', $customer?->is_active ?? true))
            class="rounded border-gray-300 text-brand-eucalyptus shadow-sm focus:ring-brand-eucalyptus">
        <x-input-label for="is_active" value="Active" class="!mb-0" />
    </div>
</div>
