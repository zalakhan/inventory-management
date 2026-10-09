<div class="bg-white shadow-sm rounded-lg p-6 space-y-4">
    <div>
        <x-input-label for="category_id" value="Category" />
        <select id="category_id" name="category_id" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-eucalyptus focus:ring-brand-eucalyptus">
            <option value="">— Select category —</option>
            @foreach ($categories as $category)
                <option value="{{ $category->id }}" @selected(old('category_id', $product?->category_id) == $category->id)>
                    {{ $category->name }}
                </option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('category_id')" class="mt-1" />
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
            <x-input-label for="code" value="Product Code" />
            <x-text-input id="code" name="code" type="text" class="mt-1 block w-full" :value="old('code', $product?->code)" required />
            <x-input-error :messages="$errors->get('code')" class="mt-1" />
        </div>
        <div>
            <x-input-label for="unit" value="Unit" />
            <x-text-input id="unit" name="unit" type="text" class="mt-1 block w-full" :value="old('unit', $product?->unit ?? 'pcs')" required />
            <x-input-error :messages="$errors->get('unit')" class="mt-1" />
        </div>
    </div>

    <div>
        <x-input-label for="name" value="Name" />
        <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $product?->name)" required />
        <x-input-error :messages="$errors->get('name')" class="mt-1" />
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div>
            <x-input-label for="purchase_rate" value="Purchase Rate" />
            <x-text-input id="purchase_rate" name="purchase_rate" type="number" step="0.0001" min="0" class="mt-1 block w-full" :value="old('purchase_rate', $product?->purchase_rate ?? 0)" />
            <x-input-error :messages="$errors->get('purchase_rate')" class="mt-1" />
        </div>
        <div>
            <x-input-label for="sale_rate" value="Sale Rate" />
            <x-text-input id="sale_rate" name="sale_rate" type="number" step="0.0001" min="0" class="mt-1 block w-full" :value="old('sale_rate', $product?->sale_rate ?? 0)" />
            <x-input-error :messages="$errors->get('sale_rate')" class="mt-1" />
        </div>
        <div>
            <x-input-label for="min_stock_level" value="Min Stock Level" />
            <x-text-input id="min_stock_level" name="min_stock_level" type="number" step="0.0001" min="0" class="mt-1 block w-full" :value="old('min_stock_level', $product?->min_stock_level ?? 0)" />
            <x-input-error :messages="$errors->get('min_stock_level')" class="mt-1" />
        </div>
    </div>

    <div>
        <x-input-label for="description" value="Description" />
        <textarea id="description" name="description" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-eucalyptus focus:ring-brand-eucalyptus">{{ old('description', $product?->description) }}</textarea>
        <x-input-error :messages="$errors->get('description')" class="mt-1" />
    </div>

    <div class="flex items-center gap-2">
        <input type="hidden" name="is_active" value="0">
        <input id="is_active" name="is_active" type="checkbox" value="1" @checked(old('is_active', $product?->is_active ?? true))
            class="rounded border-gray-300 text-brand-eucalyptus shadow-sm focus:ring-brand-eucalyptus">
        <x-input-label for="is_active" value="Active" class="!mb-0" />
    </div>
</div>
