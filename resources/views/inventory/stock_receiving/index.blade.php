<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h2 class="font-semibold text-xl text-gray-800">Stock Receiving</h2>
                <p class="mt-1 text-sm text-gray-500">Receive stock from suppliers into warehouses.</p>
            </div>
            @can('stock_receiving.create')
                <a href="{{ route('inventory.stock_receiving.create') }}" class="inline-flex items-center px-4 py-2 bg-brand-eucalyptus text-white text-sm font-medium rounded-lg hover:bg-brand-moss-shadow shrink-0">
                    New Receipt
                </a>
            @endcan
        </div>
    </x-slot>

    <div class="bg-white shadow-sm rounded-lg overflow-hidden">
        <form method="GET" class="p-4 border-b border-gray-200 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Receipt # or reference..."
                class="rounded-lg border-gray-300 shadow-sm focus:border-brand-eucalyptus focus:ring-brand-eucalyptus text-sm">
            <select name="status" class="rounded-lg border-gray-300 shadow-sm focus:border-brand-eucalyptus focus:ring-brand-eucalyptus text-sm">
                <option value="">All statuses</option>
                @foreach (['draft', 'posted', 'cancelled'] as $status)
                    <option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst($status) }}</option>
                @endforeach
            </select>
            <select name="warehouse_id" class="rounded-lg border-gray-300 shadow-sm focus:border-brand-eucalyptus focus:ring-brand-eucalyptus text-sm">
                <option value="">All warehouses</option>
                @foreach ($warehouses as $warehouse)
                    <option value="{{ $warehouse->id }}" @selected(request('warehouse_id') == $warehouse->id)>{{ $warehouse->name }}</option>
                @endforeach
            </select>
            <select name="supplier_id" class="rounded-lg border-gray-300 shadow-sm focus:border-brand-eucalyptus focus:ring-brand-eucalyptus text-sm">
                <option value="">All suppliers</option>
                @foreach ($suppliers as $supplier)
                    <option value="{{ $supplier->id }}" @selected(request('supplier_id') == $supplier->id)>{{ $supplier->name }}</option>
                @endforeach
            </select>
            <button type="submit" class="px-4 py-2 bg-brand-moss-shadow text-white text-sm rounded-lg hover:bg-brand-deep-forest">Filter</button>
        </form>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Receipt #</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Supplier</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Warehouse</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Subtotal</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse ($receivings as $receiving)
                        <tr>
                            <td class="px-4 py-3 text-sm font-medium text-gray-900">
                                <a href="{{ route('inventory.stock_receiving.show', $receiving) }}" class="text-brand-eucalyptus hover:text-brand-moss-shadow">
                                    {{ $receiving->receipt_number }}
                                </a>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-600">{{ $receiving->receiving_date->format('d M Y') }}</td>
                            <td class="px-4 py-3 text-sm text-gray-600">{{ $receiving->supplier?->name ?? '—' }}</td>
                            <td class="px-4 py-3 text-sm text-gray-600">{{ $receiving->warehouse?->name ?? '—' }}</td>
                            <td class="px-4 py-3 text-sm text-gray-600 text-right">{{ number_format($receiving->subtotal, 2) }}</td>
                            <td class="px-4 py-3 text-sm">@include('inventory.stock_receiving._status', ['status' => $receiving->status])</td>
                            <td class="px-4 py-3 text-sm text-right space-x-2">
                                <a href="{{ route('inventory.stock_receiving.show', $receiving) }}" class="text-brand-eucalyptus hover:text-brand-moss-shadow">View</a>
                                @if ($receiving->status === 'draft')
                                    @can('stock_receiving.edit')
                                        <a href="{{ route('inventory.stock_receiving.edit', $receiving) }}" class="text-brand-eucalyptus hover:text-brand-moss-shadow">Edit</a>
                                    @endcan
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-8 text-center text-sm text-gray-500">No stock receipts found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($receivings->hasPages())
            <div class="px-4 py-3 border-t border-gray-200">{{ $receivings->links() }}</div>
        @endif
    </div>
</x-app-layout>
