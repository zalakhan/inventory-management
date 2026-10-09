<div class="bg-white shadow-sm rounded-lg p-6 space-y-4">
    <div>
        <x-input-label for="code" value="Code" />
        <x-text-input id="code" name="code" type="text" class="mt-1 block w-full" :value="old('code', $warehouse?->code)" required />
        <x-input-error :messages="$errors->get('code')" class="mt-1" />
    </div>

    <div>
        <x-input-label for="name" value="Name" />
        <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $warehouse?->name)" required />
        <x-input-error :messages="$errors->get('name')" class="mt-1" />
    </div>

    <div>
        <x-input-label for="address" value="Address" />
        <textarea id="address" name="address" rows="2" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-eucalyptus focus:ring-brand-eucalyptus">{{ old('address', $warehouse?->address) }}</textarea>
        <x-input-error :messages="$errors->get('address')" class="mt-1" />
    </div>

    <div>
        <x-input-label for="phone" value="Phone" />
        <x-text-input id="phone" name="phone" type="text" class="mt-1 block w-full" :value="old('phone', $warehouse?->phone)" />
        <x-input-error :messages="$errors->get('phone')" class="mt-1" />
    </div>

    <div class="flex items-center gap-2">
        <input type="hidden" name="is_default" value="0">
        <input id="is_default" name="is_default" type="checkbox" value="1" @checked(old('is_default', $warehouse?->is_default))
            class="rounded border-gray-300 text-brand-eucalyptus shadow-sm focus:ring-brand-eucalyptus">
        <x-input-label for="is_default" value="Default warehouse" class="!mb-0" />
    </div>

    <div class="flex items-center gap-2">
        <input type="hidden" name="is_active" value="0">
        <input id="is_active" name="is_active" type="checkbox" value="1" @checked(old('is_active', $warehouse?->is_active ?? true))
            class="rounded border-gray-300 text-brand-eucalyptus shadow-sm focus:ring-brand-eucalyptus">
        <x-input-label for="is_active" value="Active" class="!mb-0" />
    </div>
</div>
