@php
    $initialItems = old('items');

    if ($initialItems === null && isset($sale)) {
        $initialItems = $sale->items->map(fn ($item) => [
            'product_id' => (string) $item->product_id,
            'quantity' => (string) $item->quantity,
            'rate' => (string) $item->rate,
            'discount_amount' => (string) $item->discount_amount,
        ])->values()->all();
    }

    if (empty($initialItems)) {
        $initialItems = [['product_id' => '', 'quantity' => '1', 'rate' => '0', 'discount_amount' => '0']];
    }
@endphp

<div class="bg-white shadow-sm rounded-lg p-6 space-y-6">
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
            <x-input-label for="customer_id" value="Customer" />
            <select id="customer_id" name="customer_id" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-eucalyptus focus:ring-brand-eucalyptus">
                <option value="">— Select customer —</option>
                @foreach ($customers as $customer)
                    <option value="{{ $customer->id }}" data-area-id="{{ $customer->area_id }}"
                        @selected(old('customer_id', $sale?->customer_id) == $customer->id)>
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
                    <option value="{{ $warehouse->id }}" @selected(old('warehouse_id', $sale?->warehouse_id ?? $defaultWarehouseId ?? null) == $warehouse->id)>
                        {{ $warehouse->name }}
                    </option>
                @endforeach
            </select>
            <x-input-error :messages="$errors->get('warehouse_id')" class="mt-1" />
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div>
            <x-input-label for="sale_date" value="Sale Date" />
            <x-text-input id="sale_date" name="sale_date" type="date" class="mt-1 block w-full"
                :value="old('sale_date', $sale?->sale_date?->format('Y-m-d') ?? now()->format('Y-m-d'))" required />
            <x-input-error :messages="$errors->get('sale_date')" class="mt-1" />
        </div>

        <div>
            <x-input-label for="salesman_id" value="Salesman" />
            <select id="salesman_id" name="salesman_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-eucalyptus focus:ring-brand-eucalyptus">
                <option value="">— Optional —</option>
                @foreach ($salesmen as $salesman)
                    <option value="{{ $salesman->id }}" data-area-id="{{ $salesman->area_id }}"
                        @selected(old('salesman_id', $sale?->salesman_id) == $salesman->id)>
                        {{ $salesman->name }}
                    </option>
                @endforeach
            </select>
            <x-input-error :messages="$errors->get('salesman_id')" class="mt-1" />
        </div>

        <div>
            <x-input-label for="area_id" value="Area" />
            <select id="area_id" name="area_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-eucalyptus focus:ring-brand-eucalyptus">
                <option value="">— Optional —</option>
                @foreach ($areas as $area)
                    <option value="{{ $area->id }}" @selected(old('area_id', $sale?->area_id) == $area->id)>
                        {{ $area->name }}
                    </option>
                @endforeach
            </select>
            <x-input-error :messages="$errors->get('area_id')" class="mt-1" />
        </div>
    </div>

    <div>
        <x-input-label for="notes" value="Notes" />
        <textarea id="notes" name="notes" rows="2" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-eucalyptus focus:ring-brand-eucalyptus">{{ old('notes', $sale?->notes) }}</textarea>
        <x-input-error :messages="$errors->get('notes')" class="mt-1" />
    </div>

    {{-- Line items with Alpine.js --}}
    <div x-data="{
        products: @js($products),
        lines: @js($initialItems),
        invoiceDiscount: @js(old('discount_amount', $sale?->discount_amount ?? '0')),
        amountPaid: @js(old('amount_paid', $sale?->amount_paid ?? '0')),
        addLine() { this.lines.push({ product_id: '', quantity: '1', rate: '0', discount_amount: '0' }); },
        removeLine(index) { if (this.lines.length > 1) this.lines.splice(index, 1); },
        onProductChange(index) {
            const product = this.products.find(p => String(p.id) === String(this.lines[index].product_id));
            if (product && product.sale_rate) this.lines[index].rate = String(product.sale_rate);
        },
        lineGross(line) { return ((parseFloat(line.quantity) || 0) * (parseFloat(line.rate) || 0)); },
        lineTotal(line) { return Math.max(0, this.lineGross(line) - (parseFloat(line.discount_amount) || 0)).toFixed(2); },
        subtotal() { return this.lines.reduce((sum, line) => sum + parseFloat(this.lineTotal(line)), 0).toFixed(2); },
        totalAmount() { return Math.max(0, parseFloat(this.subtotal()) - (parseFloat(this.invoiceDiscount) || 0)).toFixed(2); },
        outstanding() { return Math.max(0, parseFloat(this.totalAmount()) - (parseFloat(this.amountPaid) || 0)).toFixed(2); }
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
                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase w-24">Qty</th>
                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase w-28">Rate</th>
                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase w-28">Disc.</th>
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
                            <td class="px-3 py-2">
                                <input type="number" step="0.01" min="0" :name="'items[' + index + '][discount_amount]'" x-model="line.discount_amount"
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

        <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div class="sm:col-start-2 space-y-3 bg-gray-50 rounded-lg p-4">
                <div class="flex justify-between text-sm">
                    <span class="text-gray-600">Subtotal</span>
                    <span class="font-medium text-gray-900" x-text="subtotal()"></span>
                </div>
                <div class="flex justify-between items-center text-sm gap-3">
                    <label for="discount_amount" class="text-gray-600 shrink-0">Invoice Discount</label>
                    <input type="number" step="0.01" min="0" id="discount_amount" name="discount_amount" x-model="invoiceDiscount"
                        class="w-32 rounded-md border-gray-300 shadow-sm focus:border-brand-eucalyptus focus:ring-brand-eucalyptus text-sm text-right">
                </div>
                <div class="flex justify-between text-sm font-semibold border-t border-gray-200 pt-2">
                    <span class="text-gray-700">Total</span>
                    <span class="text-gray-900" x-text="totalAmount()"></span>
                </div>
                <div class="flex justify-between items-center text-sm gap-3">
                    <label for="amount_paid" class="text-gray-600 shrink-0">Amount Paid</label>
                    <input type="number" step="0.01" min="0" id="amount_paid" name="amount_paid" x-model="amountPaid"
                        class="w-32 rounded-md border-gray-300 shadow-sm focus:border-brand-eucalyptus focus:ring-brand-eucalyptus text-sm text-right">
                </div>
                <div class="flex justify-between text-sm font-semibold">
                    <span class="text-gray-700">Outstanding</span>
                    <span class="text-brand-eucalyptus" x-text="outstanding()"></span>
                </div>
            </div>
        </div>
        <x-input-error :messages="$errors->get('discount_amount')" class="mt-2" />
        <x-input-error :messages="$errors->get('amount_paid')" class="mt-1" />
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const customerSelect = document.getElementById('customer_id');
        const areaSelect = document.getElementById('area_id');

        if (customerSelect && areaSelect) {
            customerSelect.addEventListener('change', function () {
                const selected = customerSelect.options[customerSelect.selectedIndex];
                const areaId = selected?.dataset?.areaId;
                if (areaId && !areaSelect.value) {
                    areaSelect.value = areaId;
                }
            });
        }
    });
</script>
