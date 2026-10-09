<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h2 class="font-semibold text-xl text-gray-800">Stock Summary</h2>
                <p class="mt-1 text-sm text-gray-500">Current stock levels and inventory value.</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('reports.index') }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 text-sm rounded-lg hover:bg-gray-50">All Reports</a>
                @include('reports._export-button')
            </div>
        </div>
    </x-slot>

    <div class="bg-white shadow-sm rounded-lg overflow-hidden">
        <form method="GET" class="p-4 border-b border-gray-200 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
            <select name="warehouse_id" class="rounded-lg border-gray-300 shadow-sm focus:border-brand-eucalyptus focus:ring-brand-eucalyptus text-sm">
                <option value="">All warehouses</option>
                @foreach ($warehouses as $warehouse)
                    <option value="{{ $warehouse->id }}" @selected(request('warehouse_id') == $warehouse->id)>{{ $warehouse->name }}</option>
                @endforeach
            </select>
            <select name="category_id" class="rounded-lg border-gray-300 shadow-sm focus:border-brand-eucalyptus focus:ring-brand-eucalyptus text-sm">
                <option value="">All categories</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}" @selected(request('category_id') == $category->id)>{{ $category->name }}</option>
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
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Purchase Rate</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Stock Value</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse ($rows as $row)
                        @php
                            $stockValue = bcmul((string) $row->quantity, (string) ($row->product?->purchase_rate ?? 0), 2);
                        @endphp
                        <tr>
                            <td class="px-4 py-3 text-sm text-gray-600">{{ $row->warehouse?->name ?? '—' }}</td>
                            <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $row->product?->code }} — {{ $row->product?->name }}</td>
                            <td class="px-4 py-3 text-sm text-gray-600">{{ $row->product?->category?->name ?? '—' }}</td>
                            <td class="px-4 py-3 text-sm text-gray-900 text-right">{{ number_format($row->quantity, 4) }}</td>
                            <td class="px-4 py-3 text-sm text-gray-600 text-right">{{ number_format($row->product?->purchase_rate ?? 0, 4) }}</td>
                            <td class="px-4 py-3 text-sm text-gray-900 text-right font-medium">{{ number_format((float) $stockValue, 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-sm text-gray-500">No stock records found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($rows->hasPages())
            <div class="px-4 py-3 border-t border-gray-200">{{ $rows->links() }}</div>
        @endif
    </div>
</x-app-layout>
