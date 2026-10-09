<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h2 class="font-semibold text-xl text-gray-800">Stock Ledger</h2>
                <p class="mt-1 text-sm text-gray-500">Auditable history of all stock movements.</p>
            </div>
            <a href="{{ route('inventory.stock.index') }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 text-sm rounded-lg hover:bg-gray-50 shrink-0">
                Current Stock
            </a>
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
            <select name="product_id" class="rounded-lg border-gray-300 shadow-sm focus:border-brand-eucalyptus focus:ring-brand-eucalyptus text-sm">
                <option value="">All products</option>
                @foreach ($products as $product)
                    <option value="{{ $product->id }}" @selected(request('product_id') == $product->id)>{{ $product->code }} — {{ $product->name }}</option>
                @endforeach
            </select>
            <select name="direction" class="rounded-lg border-gray-300 shadow-sm focus:border-brand-eucalyptus focus:ring-brand-eucalyptus text-sm">
                <option value="">All directions</option>
                <option value="in" @selected(request('direction') === 'in')>In</option>
                <option value="out" @selected(request('direction') === 'out')>Out</option>
            </select>
            <select name="status" class="rounded-lg border-gray-300 shadow-sm focus:border-brand-eucalyptus focus:ring-brand-eucalyptus text-sm">
                <option value="">All statuses</option>
                <option value="posted" @selected(request('status') === 'posted')>Posted</option>
                <option value="cancelled" @selected(request('status') === 'cancelled')>Cancelled</option>
            </select>
            <input type="date" name="date_from" value="{{ request('date_from') }}" class="rounded-lg border-gray-300 shadow-sm focus:border-brand-eucalyptus focus:ring-brand-eucalyptus text-sm">
            <input type="date" name="date_to" value="{{ request('date_to') }}" class="rounded-lg border-gray-300 shadow-sm focus:border-brand-eucalyptus focus:ring-brand-eucalyptus text-sm">
            <button type="submit" class="px-4 py-2 bg-brand-moss-shadow text-white text-sm rounded-lg hover:bg-brand-deep-forest sm:col-span-2">Filter</button>
        </form>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Warehouse</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Product</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Direction</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Qty</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Balance After</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Source</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse ($movements as $movement)
                        <tr @class(['opacity-60' => $movement->status === 'cancelled'])>
                            <td class="px-4 py-3 text-sm text-gray-600">{{ $movement->movement_date->format('d M Y') }}</td>
                            <td class="px-4 py-3 text-sm text-gray-600">{{ $movement->warehouse?->name ?? '—' }}</td>
                            <td class="px-4 py-3 text-sm text-gray-900">{{ $movement->product?->code }} — {{ $movement->product?->name }}</td>
                            <td class="px-4 py-3 text-sm">
                                @if ($movement->direction === 'in')
                                    <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">In</span>
                                @else
                                    <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-800">Out</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-900 text-right">{{ number_format($movement->quantity, 4) }}</td>
                            <td class="px-4 py-3 text-sm text-gray-600 text-right">{{ number_format($movement->balance_after, 4) }}</td>
                            <td class="px-4 py-3 text-sm text-gray-600">
                                @if ($movement->source_type === \App\Models\StockReceiving::class && $movement->source)
                                    <a href="{{ route('inventory.stock_receiving.show', $movement->source) }}" class="text-brand-eucalyptus hover:text-brand-moss-shadow">
                                        {{ $movement->source->receipt_number ?? 'Receiving' }}
                                    </a>
                                @else
                                    {{ class_basename($movement->source_type ?? '') }} #{{ $movement->source_id }}
                                @endif
                                @if ($movement->reversal_of_id)
                                    <span class="block text-xs text-gray-500">Reversal</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-sm">
                                @if ($movement->status === 'posted')
                                    <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Posted</span>
                                @else
                                    <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-600">Cancelled</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-8 text-center text-sm text-gray-500">No stock movements found.</td>
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
