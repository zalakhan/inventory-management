@php
    $initialItems = old('items');

    if ($initialItems === null && isset($receiving)) {
        $initialItems = $receiving->items->map(fn ($item) => [
            'product_id' => (string) $item->product_id,
            'quantity' => (string) $item->quantity,
            'purchase_rate' => (string) $item->purchase_rate,
        ])->values()->all();
    }

    if (empty($initialItems)) {
        $initialItems = [['product_id' => '', 'quantity' => '1', 'purchase_rate' => '0']];
    }
@endphp

<div class="bg-white shadow-sm rounded-lg p-6 space-y-6">
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
            <x-input-label for="supplier_id" value="Supplier" />
            <select id="supplier_id" name="supplier_id" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-eucalyptus focus:ring-brand-eucalyptus">
                <option value="">— Select supplier —</option>
                @foreach ($suppliers as $supplier)
                    <option value="{{ $supplier->id }}" @selected(old('supplier_id', $receiving?->supplier_id) == $supplier->id)>
                        {{ $supplier->name }}
                    </option>
                @endforeach
            </select>
            <x-input-error :messages="$errors->get('supplier_id')" class="mt-1" />
        </div>

        <div>
            <x-input-label for="warehouse_id" value="Warehouse" />
            <select id="warehouse_id" name="warehouse_id" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-eucalyptus focus:ring-brand-eucalyptus">
                <option value="">— Select warehouse —</option>
                @foreach ($warehouses as $warehouse)
                    <option value="{{ $warehouse->id }}" @selected(old('warehouse_id', $receiving?->warehouse_id ?? $defaultWarehouseId ?? null) == $warehouse->id)>
                        {{ $warehouse->name }}
                    </option>
                @endforeach
            </select>
            <x-input-error :messages="$errors->get('warehouse_id')" class="mt-1" />
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
            <x-input-label for="receiving_date" value="Receiving Date" />
            <x-text-input id="receiving_date" name="receiving_date" type="date" class="mt-1 block w-full"
                :value="old('receiving_date', $receiving?->receiving_date?->format('Y-m-d') ?? now()->format('Y-m-d'))" required />
            <x-input-error :messages="$errors->get('receiving_date')" class="mt-1" />
        </div>

        <div>
            <x-input-label for="supplier_reference" value="Supplier Reference" />
            <x-text-input id="supplier_reference" name="supplier_reference" type="text" class="mt-1 block w-full"
                :value="old('supplier_reference', $receiving?->supplier_reference)" placeholder="Supplier invoice #" />
            <x-input-error :messages="$errors->get('supplier_reference')" class="mt-1" />
        </div>
    </div>

    <div>
        <x-input-label for="notes" value="Notes" />
        <textarea id="notes" name="notes" rows="2" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-eucalyptus focus:ring-brand-eucalyptus">{{ old('notes', $receiving?->notes) }}</textarea>
        <x-input-error :messages="$errors->get('notes')" class="mt-1" />
    </div>

    {{-- Line items with Alpine.js --}}
    <div x-data="{
        products: @js($products),
        lines: @js($initialItems),
        addLine() { this.lines.push({ product_id: '', quantity: '1', purchase_rate: '0' }); },
        removeLine(index) { if (this.lines.length > 1) this.lines.splice(index, 1); },
        onProductChange(index) {
            const product = this.products.find(p => String(p.id) === String(this.lines[index].product_id));
            if (product && product.purchase_rate) this.lines[index].purchase_rate = String(product.purchase_rate);
        },
        lineTotal(line) { return ((parseFloat(line.quantity) || 0) * (parseFloat(line.purchase_rate) || 0)).toFixed(2); },
        subtotal() { return this.lines.reduce((sum, line) => sum + ((parseFloat(line.quantity) || 0) * (parseFloat(line.purchase_rate) || 0)), 0).toFixed(2); }
    }">
        <div class="flex items-center justify-between mb-3">
            <x-input-label value="Line Items" class="!mb-0" />
            <button type="button" @click="addLine()" class="text-sm text-brand-eucalyptus hover:text-brand-moss-shadow font-medium">
                + Add Line
            </button>
        </div>

        @if ($errors->has('items') || $errors->has('items.*'))
            <div class="mb-3 text-sm text-red-600">
                {{ $errors->first('items') ?: 'Please check line item fields.' }}
            </div>
        @endif

        <div class="overflow-x-auto border border-gray-200 rounded-lg">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Product</th>
                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase w-28">Qty</th>
                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase w-32">Rate</th>
                        <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase w-28">Total</th>
                        <th class="px-3 py-2 w-10"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <template x-for="(line, index) in lines" :key="index">
                        <tr>
                            <td class="px-3 py-2">
                                <select :name="'items[' + index + '][product_id]'" x-model="line.product_id" @change="onProductChange(index)" required
                                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-eucalyptus focus:ring-brand-eucalyptus text-sm">
                                    <option value="">— Select —</option>
                                    <template x-for="product in products" :key="product.id">
                                        <option :value="product.id" x-text="product.code + ' — ' + product.name"></option>
                                    </template>
                                </select>
                            </td>
                            <td class="px-3 py-2">
                                <input type="number" step="0.0001" min="0.0001" :name="'items[' + index + '][quantity]'" x-model="line.quantity" required
                                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-eucalyptus focus:ring-brand-eucalyptus text-sm">
                            </td>
                            <td class="px-3 py-2">
                                <input type="number" step="0.0001" min="0" :name="'items[' + index + '][purchase_rate]'" x-model="line.purchase_rate" required
                                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-eucalyptus focus:ring-brand-eucalyptus text-sm">
                            </td>
                            <td class="px-3 py-2 text-sm text-gray-700 text-right" x-text="lineTotal(line)"></td>
                            <td class="px-3 py-2 text-center">
                                <button type="button" @click="removeLine(index)" x-show="lines.length > 1" class="text-red-600 hover:text-red-900 text-sm">×</button>
                            </td>
                        </tr>
                    </template>
                </tbody>
                <tfoot class="bg-gray-50">
                    <tr>
                        <td colspan="3" class="px-3 py-2 text-sm font-medium text-gray-700 text-right">Subtotal</td>
                        <td class="px-3 py-2 text-sm font-semibold text-gray-900 text-right" x-text="subtotal()"></td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
