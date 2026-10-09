<div class="bg-white shadow-sm rounded-lg p-6 space-y-4">
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
            <x-input-label for="code" value="Supplier Code" />
            <x-text-input id="code" name="code" type="text" class="mt-1 block w-full" :value="old('code', $supplier?->code)" required />
            <x-input-error :messages="$errors->get('code')" class="mt-1" />
        </div>
        <div>
            <x-input-label for="name" value="Company Name" />
            <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $supplier?->name)" required />
            <x-input-error :messages="$errors->get('name')" class="mt-1" />
        </div>
    </div>

    <div>
        <x-input-label for="contact_person" value="Contact Person" />
        <x-text-input id="contact_person" name="contact_person" type="text" class="mt-1 block w-full" :value="old('contact_person', $supplier?->contact_person)" />
        <x-input-error :messages="$errors->get('contact_person')" class="mt-1" />
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
            <x-input-label for="phone" value="Phone" />
            <x-text-input id="phone" name="phone" type="text" class="mt-1 block w-full" :value="old('phone', $supplier?->phone)" />
            <x-input-error :messages="$errors->get('phone')" class="mt-1" />
        </div>
        <div>
            <x-input-label for="email" value="Email" />
            <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email', $supplier?->email)" />
            <x-input-error :messages="$errors->get('email')" class="mt-1" />
        </div>
    </div>

    <div>
        <x-input-label for="address" value="Address" />
        <textarea id="address" name="address" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-eucalyptus focus:ring-brand-eucalyptus">{{ old('address', $supplier?->address) }}</textarea>
        <x-input-error :messages="$errors->get('address')" class="mt-1" />
    </div>

    <div class="flex items-center gap-2">
        <input type="hidden" name="is_active" value="0">
        <input id="is_active" name="is_active" type="checkbox" value="1" @checked(old('is_active', $supplier?->is_active ?? true))
            class="rounded border-gray-300 text-brand-eucalyptus shadow-sm focus:ring-brand-eucalyptus">
        <x-input-label for="is_active" value="Active" class="!mb-0" />
    </div>
</div>
