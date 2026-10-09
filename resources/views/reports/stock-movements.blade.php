<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h2 class="font-semibold text-xl text-gray-800">Stock Movement Report</h2>
                <p class="mt-1 text-sm text-gray-500">Posted stock IN/OUT movements.</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('reports.index') }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 text-sm rounded-lg hover:bg-gray-50">All Reports</a>
                @include('reports._export-button')
            </div>
        </div>
    </x-slot>

    <div class="bg-white shadow-sm rounded-lg overflow-hidden">
        <form method="GET" class="p-4 border-b border-gray-200 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-3">
            <input type="date" name="date_from" value="{{ request('date_from') }}"
                class="rounded-lg border-gray-300 shadow-sm focus:border-brand-eucalyptus focus:ring-brand-eucalyptus text-sm">
            <input type="date" name="date_to" value="{{ request('date_to') }}"
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
            <select name="direction" class="rounded-lg border-gray-300 shadow-sm focus:border-brand-eucalyptus focus:ring-brand-eucalyptus text-sm">
                <option value="">All directions</option>
                <option value="in" @selected(request('direction') === 'in')>IN</option>
                <option value="out" @selected(request('direction') === 'out')>OUT</option>
            </select>
            <button type="submit" class="px-4 py-2 bg-brand-moss-shadow text-white text-sm rounded-lg hover:bg-brand-deep-forest">Filter</button>
        </form>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Warehouse</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Product</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Direction</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Quantity</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Balance After</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Source</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse ($movements as $movement)
                        <tr>
                            <td class="px-4 py-3 text-sm text-gray-600">{{ $movement->movement_date?->format('M d, Y') }}</td>
                            <td class="px-4 py-3 text-sm text-gray-600">{{ $movement->warehouse?->name ?? '—' }}</td>
                            <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $movement->product?->code }} — {{ $movement->product?->name }}</td>
                            <td class="px-4 py-3 text-sm">
                                <span @class([
                                    'inline-flex px-2.5 py-0.5 rounded-full text-xs font-medium',
                                    'bg-green-100 text-green-800' => $movement->direction === 'in',
                                    'bg-red-100 text-red-800' => $movement->direction === 'out',
                                ])>{{ strtoupper($movement->direction) }}</span>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-900 text-right">{{ number_format($movement->quantity, 4) }}</td>
                            <td class="px-4 py-3 text-sm text-gray-600 text-right">{{ number_format($movement->balance_after, 4) }}</td>
                            <td class="px-4 py-3 text-sm text-gray-600">{{ class_basename($movement->source_type ?? '') ?: '—' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-8 text-center text-sm text-gray-500">No movements found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($movements->hasPages())
            <div class="px-4 py-3 border-t border-gray-200">{{ $movements->links() }}</div>
        @endif
    </div>
</x-app-layout>
