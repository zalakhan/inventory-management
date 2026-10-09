<div class="bg-white shadow-sm rounded-lg p-6 space-y-4">
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
            <x-input-label for="code" value="Area Code" />
            <x-text-input id="code" name="code" type="text" class="mt-1 block w-full" :value="old('code', $area?->code)" required />
            <x-input-error :messages="$errors->get('code')" class="mt-1" />
        </div>
        <div>
            <x-input-label for="name" value="Name" />
            <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $area?->name)" required />
            <x-input-error :messages="$errors->get('name')" class="mt-1" />
        </div>
    </div>

    <div>
        <x-input-label for="description" value="Description" />
        <textarea id="description" name="description" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-eucalyptus focus:ring-brand-eucalyptus">{{ old('description', $area?->description) }}</textarea>
        <x-input-error :messages="$errors->get('description')" class="mt-1" />
    </div>

    <div class="flex items-center gap-2">
        <input type="hidden" name="is_active" value="0">
        <input id="is_active" name="is_active" type="checkbox" value="1" @checked(old('is_active', $area?->is_active ?? true))
            class="rounded border-gray-300 text-brand-eucalyptus shadow-sm focus:ring-brand-eucalyptus">
        <x-input-label for="is_active" value="Active" class="!mb-0" />
    </div>
</div>
