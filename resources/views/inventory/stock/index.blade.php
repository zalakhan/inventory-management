<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h2 class="font-semibold text-xl text-gray-800">Current Stock</h2>
                <p class="mt-1 text-sm text-gray-500">Stock levels by product and warehouse.</p>
            </div>
            @can('inventory.view')
                <a href="{{ route('inventory.movements.index') }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 text-sm rounded-lg hover:bg-gray-50 shrink-0">
                    Stock Ledger
                </a>
            @endcan
        </div>
    </x-slot>

    <div class="bg-white shadow-sm rounded-lg overflow-hidden">
        <form method="GET" class="p-4 border-b border-gray-200 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Product code or name..."
                class="rounded-lg border-gray-300 shadow-sm focus:border-brand-eucalyptus focus:ring-brand-eucalyptus text-sm">
            <select name="warehouse_id" class="rounded-lg border-gray-300 shadow-sm focus:border-brand-eucalyptus focus:ring-brand-eucalyptus text-sm">
                <option value="">All warehouses</option>
                @foreach ($warehouses as $warehouse)
                    <option value="{{ $warehouse->id }}" @selected(request('warehouse_id') == $warehouse->id)>{{ $warehouse->name }}</option>
                @endforeach
            </select>
            <select name="product_id" class="rounded-lg border-gray-300 shadow-sm focus:border-brand-eucalyptus focus:ring-brand-eucalyptus text-sm">
                <option value="">All products</option>
                @foreach ($products as $product)
                    <option value="{{ $product->id }}" @selected(request('product_id') == $product->id)>{{ $product->code }} — {{ $product->name }}</option>
                @endforeach
            </select>
            <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                <input type="checkbox" name="low_stock" value="1" @checked(request()->boolean('low_stock'))
                    class="rounded border-gray-300 text-brand-eucalyptus shadow-sm focus:ring-brand-eucalyptus">
                Low stock only
            </label>
            <button type="submit" class="px-4 py-2 bg-brand-moss-shadow text-white text-sm rounded-lg hover:bg-brand-deep-forest">Filter</button>
        </form>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Warehouse</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Product</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Category</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Quantity</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Min Level</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse ($stock as $row)
                        @php
                            $isLow = $row->product && $row->product->min_stock_level > 0 && $row->quantity <= $row->product->min_stock_level;
                        @endphp
                        <tr @class(['bg-red-50' => $isLow])>
                            <td class="px-4 py-3 text-sm text-gray-600">{{ $row->warehouse?->name ?? '—' }}</td>
                            <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $row->product?->code }} — {{ $row->product?->name }}</td>
                            <td class="px-4 py-3 text-sm text-gray-600">{{ $row->product?->category?->name ?? '—' }}</td>
                            <td class="px-4 py-3 text-sm text-gray-900 text-right font-medium">{{ number_format($row->quantity, 4) }}</td>
                            <td class="px-4 py-3 text-sm text-gray-600 text-right">{{ number_format($row->product?->min_stock_level ?? 0, 4) }}</td>
                            <td class="px-4 py-3 text-sm">
                                @if ($isLow)
                                    <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">Low</span>
                                @else
                                    <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">OK</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-sm text-gray-500">No stock records found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($stock->hasPages())
            <div class="px-4 py-3 border-t border-gray-200">{{ $stock->links() }}</div>
        @endif
    </div>
</x-app-layout>
