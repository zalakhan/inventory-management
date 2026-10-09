@php
    $initialItems = old('items');

    if ($initialItems === null && isset($saleReturn)) {
        $initialItems = $saleReturn->items->map(fn ($item) => [
            'product_id' => (string) $item->product_id,
            'quantity' => (string) $item->quantity,
            'rate' => (string) $item->rate,
        ])->values()->all();
    }

    if (empty($initialItems)) {
        $initialItems = [['product_id' => '', 'quantity' => '1', 'rate' => '0']];
    }
@endphp

<div class="bg-white shadow-sm rounded-lg p-6 space-y-6">
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
            <x-input-label for="customer_id" value="Customer" />
            <select id="customer_id" name="customer_id" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-eucalyptus focus:ring-brand-eucalyptus">
                <option value="">— Select customer —</option>
                @foreach ($customers as $customer)
                    <option value="{{ $customer->id }}" @selected(old('customer_id', $saleReturn?->customer_id) == $customer->id)>
                        {{ $customer->code }} — {{ $customer->name }}
                    </option>
                @endforeach
            </select>
            <x-input-error :messages="$errors->get('customer_id')" class="mt-1" />
        </div>

        <div>
            <x-input-label for="warehouse_id" value="Warehouse" />
            <select id="warehouse_id" name="warehouse_id" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-eucalyptus focus:ring-brand-eucalyptus">
                <option value="">— Select warehouse —</option>
                @foreach ($warehouses as $warehouse)
                    <option value="{{ $warehouse->id }}" @selected(old('warehouse_id', $saleReturn?->warehouse_id ?? $defaultWarehouseId ?? null) == $warehouse->id)>
                        {{ $warehouse->name }}
                    </option>
                @endforeach
            </select>
            <x-input-error :messages="$errors->get('warehouse_id')" class="mt-1" />
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div>
            <x-input-label for="return_date" value="Return Date" />
            <x-text-input id="return_date" name="return_date" type="date" class="mt-1 block w-full"
                :value="old('return_date', $saleReturn?->return_date?->format('Y-m-d') ?? now()->format('Y-m-d'))" required />
            <x-input-error :messages="$errors->get('return_date')" class="mt-1" />
        </div>

        <div>
            <x-input-label for="sale_id" value="Original Invoice" />
            <select id="sale_id" name="sale_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-eucalyptus focus:ring-brand-eucalyptus">
                <option value="">— Optional —</option>
                @foreach ($sales as $sale)
                    <option value="{{ $sale->id }}" data-customer-id="{{ $sale->customer_id }}"
                        @selected(old('sale_id', $saleReturn?->sale_id) == $sale->id)>
                        {{ $sale->invoice_number }}
                    </option>
                @endforeach
            </select>
            <x-input-error :messages="$errors->get('sale_id')" class="mt-1" />
        </div>

        <div>
            <x-input-label for="reason" value="Return Reason" />
            <x-text-input id="reason" name="reason" type="text" class="mt-1 block w-full"
                :value="old('reason', $saleReturn?->reason)" />
            <x-input-error :messages="$errors->get('reason')" class="mt-1" />
        </div>
    </div>

    <div>
        <x-input-label for="notes" value="Notes" />
        <textarea id="notes" name="notes" rows="2" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-eucalyptus focus:ring-brand-eucalyptus">{{ old('notes', $saleReturn?->notes) }}</textarea>
        <x-input-error :messages="$errors->get('notes')" class="mt-1" />
    </div>

    <div x-data="{
        products: @js($products),
        lines: @js($initialItems),
        addLine() { this.lines.push({ product_id: '', quantity: '1', rate: '0' }); },
        removeLine(index) { if (this.lines.length > 1) this.lines.splice(index, 1); },
        onProductChange(index) {
            const product = this.products.find(p => String(p.id) === String(this.lines[index].product_id));
            if (product && product.sale_rate) this.lines[index].rate = String(product.sale_rate);
        },
        lineTotal(line) { return ((parseFloat(line.quantity) || 0) * (parseFloat(line.rate) || 0)).toFixed(2); },
        subtotal() { return this.lines.reduce((sum, line) => sum + parseFloat(this.lineTotal(line)), 0).toFixed(2); }
    }">
        <div class="flex items-center justify-between mb-3">
            <x-input-label value="Line Items" class="!mb-0" />
            <button type="button" @click="addLine()" class="text-sm text-brand-eucalyptus hover:text-brand-moss-shadow font-medium">+ Add Line</button>
        </div>

        @if ($errors->has('items') || $errors->has('items.*'))
            <div class="mb-3 text-sm text-red-600">{{ $errors->first('items') ?: 'Please check line item fields.' }}</div>
        @endif

        <div class="overflow-x-auto border border-gray-200 rounded-lg">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Product</th>
                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase w-24">Qty</th>
                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase w-28">Rate</th>
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
                                <input type="number" step="0.0001" min="0" :name="'items[' + index + '][rate]'" x-model="line.rate" required
                                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-eucalyptus focus:ring-brand-eucalyptus text-sm">
                            </td>
                            <td class="px-3 py-2 text-sm text-gray-700 text-right" x-text="lineTotal(line)"></td>
                            <td class="px-3 py-2 text-center">
                                <button type="button" @click="removeLine(index)" x-show="lines.length > 1" class="text-red-600 hover:text-red-900 text-sm">×</button>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>

        <div class="mt-4 flex justify-end">
            <div class="bg-gray-50 rounded-lg p-4 w-full sm:w-64 space-y-2 text-sm">
                <div class="flex justify-between font-semibold">
                    <span class="text-gray-700">Total Credit</span>
                    <span class="text-gray-900" x-text="subtotal()"></span>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const customerSelect = document.getElementById('customer_id');
        const saleSelect = document.getElementById('sale_id');

        if (customerSelect && saleSelect) {
            saleSelect.addEventListener('change', function () {
                const selected = saleSelect.options[saleSelect.selectedIndex];
                const customerId = selected?.dataset?.customerId;
                if (customerId) customerSelect.value = customerId;
            });
        }
    });
</script>
