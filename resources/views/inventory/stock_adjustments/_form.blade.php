@php
    $initialItems = old('items');

    if ($initialItems === null && isset($adjustment)) {
        $initialItems = $adjustment->items->map(fn ($item) => [
            'product_id' => (string) $item->product_id,
            'direction' => $item->direction,
            'quantity' => (string) $item->quantity,
            'notes' => $item->notes ?? '',
        ])->values()->all();
    }

    if (empty($initialItems)) {
        $initialItems = [['product_id' => '', 'direction' => 'out', 'quantity' => '1', 'notes' => '']];
    }
@endphp

<div class="bg-white shadow-sm rounded-lg p-6 space-y-6">
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
            <x-input-label for="warehouse_id" value="Warehouse" />
            <select id="warehouse_id" name="warehouse_id" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-eucalyptus focus:ring-brand-eucalyptus">
                <option value="">— Select warehouse —</option>
                @foreach ($warehouses as $warehouse)
                    <option value="{{ $warehouse->id }}" @selected(old('warehouse_id', $adjustment?->warehouse_id ?? $defaultWarehouseId ?? null) == $warehouse->id)>
                        {{ $warehouse->name }}
                    </option>
                @endforeach
            </select>
            <x-input-error :messages="$errors->get('warehouse_id')" class="mt-1" />
        </div>

        <div>
            <x-input-label for="adjustment_date" value="Adjustment Date" />
            <x-text-input id="adjustment_date" name="adjustment_date" type="date" class="mt-1 block w-full"
                :value="old('adjustment_date', $adjustment?->adjustment_date?->format('Y-m-d') ?? now()->format('Y-m-d'))" required />
            <x-input-error :messages="$errors->get('adjustment_date')" class="mt-1" />
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
            <x-input-label for="adjustment_type" value="Adjustment Type" />
            <select id="adjustment_type" name="adjustment_type" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-eucalyptus focus:ring-brand-eucalyptus">
                @foreach (['damaged' => 'Damaged', 'expired' => 'Expired', 'found' => 'Found', 'correction' => 'Correction', 'other' => 'Other'] as $value => $label)
                    <option value="{{ $value }}" @selected(old('adjustment_type', $adjustment?->adjustment_type ?? 'correction') === $value)>{{ $label }}</option>
                @endforeach
            </select>
            <x-input-error :messages="$errors->get('adjustment_type')" class="mt-1" />
        </div>

        <div>
            <x-input-label for="reason" value="Reason" />
            <x-text-input id="reason" name="reason" type="text" class="mt-1 block w-full"
                :value="old('reason', $adjustment?->reason)" />
            <x-input-error :messages="$errors->get('reason')" class="mt-1" />
        </div>
    </div>

    <div>
        <x-input-label for="notes" value="Notes" />
        <textarea id="notes" name="notes" rows="2" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-eucalyptus focus:ring-brand-eucalyptus">{{ old('notes', $adjustment?->notes) }}</textarea>
        <x-input-error :messages="$errors->get('notes')" class="mt-1" />
    </div>

    <div x-data="{
        products: @js($products),
        lines: @js($initialItems),
        addLine() { this.lines.push({ product_id: '', direction: 'out', quantity: '1', notes: '' }); },
        removeLine(index) { if (this.lines.length > 1) this.lines.splice(index, 1); }
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
                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase w-28">Direction</th>
                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase w-24">Qty</th>
                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Notes</th>
                        <th class="px-3 py-2 w-10"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <template x-for="(line, index) in lines" :key="index">
                        <tr>
                            <td class="px-3 py-2">
                                <select :name="'items[' + index + '][product_id]'" x-model="line.product_id" required
                                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-eucalyptus focus:ring-brand-eucalyptus text-sm">
                                    <option value="">— Select —</option>
                                    <template x-for="product in products" :key="product.id">
                                        <option :value="product.id" x-text="product.code + ' — ' + product.name"></option>
                                    </template>
                                </select>
                            </td>
                            <td class="px-3 py-2">
                                <select :name="'items[' + index + '][direction]'" x-model="line.direction" required
                                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-eucalyptus focus:ring-brand-eucalyptus text-sm">
                                    <option value="in">Stock In</option>
                                    <option value="out">Stock Out</option>
                                </select>
                            </td>
                            <td class="px-3 py-2">
                                <input type="number" step="0.0001" min="0.0001" :name="'items[' + index + '][quantity]'" x-model="line.quantity" required
                                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-eucalyptus focus:ring-brand-eucalyptus text-sm">
                            </td>
                            <td class="px-3 py-2">
                                <input type="text" :name="'items[' + index + '][notes]'" x-model="line.notes"
                                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-eucalyptus focus:ring-brand-eucalyptus text-sm">
                            </td>
                            <td class="px-3 py-2 text-center">
                                <button type="button" @click="removeLine(index)" x-show="lines.length > 1" class="text-red-600 hover:text-red-900 text-sm">×</button>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </div>
</div>
