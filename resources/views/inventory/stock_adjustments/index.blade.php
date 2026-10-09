<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h2 class="font-semibold text-xl text-gray-800">Stock Adjustments</h2>
                <p class="mt-1 text-sm text-gray-500">Adjust stock for damaged, expired, found, or correction items.</p>
            </div>
            @can('stock_adjustments.create')
                <a href="{{ route('inventory.stock_adjustments.create') }}" class="inline-flex items-center px-4 py-2 bg-brand-eucalyptus text-white text-sm font-medium rounded-lg hover:bg-brand-moss-shadow shrink-0">
                    New Adjustment
                </a>
            @endcan
        </div>
    </x-slot>

    <div class="bg-white shadow-sm rounded-lg overflow-hidden">
        <form method="GET" class="p-4 border-b border-gray-200 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Adjustment #..."
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
            <select name="adjustment_type" class="rounded-lg border-gray-300 shadow-sm focus:border-brand-eucalyptus focus:ring-brand-eucalyptus text-sm">
                <option value="">All types</option>
                @foreach (['damaged', 'expired', 'found', 'correction', 'other'] as $type)
                    <option value="{{ $type }}" @selected(request('adjustment_type') === $type)>{{ ucfirst($type) }}</option>
                @endforeach
            </select>
            <button type="submit" class="px-4 py-2 bg-brand-moss-shadow text-white text-sm rounded-lg hover:bg-brand-deep-forest">Filter</button>
        </form>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Adjustment #</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Warehouse</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse ($adjustments as $adjustment)
                        <tr>
                            <td class="px-4 py-3 text-sm font-medium text-gray-900">
                                <a href="{{ route('inventory.stock_adjustments.show', $adjustment) }}" class="text-brand-eucalyptus hover:text-brand-moss-shadow">
                                    {{ $adjustment->adjustment_number }}
                                </a>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-600">{{ $adjustment->adjustment_date->format('d M Y') }}</td>
                            <td class="px-4 py-3 text-sm text-gray-600">{{ $adjustment->warehouse?->name ?? '—' }}</td>
                            <td class="px-4 py-3 text-sm text-gray-600">{{ ucfirst($adjustment->adjustment_type) }}</td>
                            <td class="px-4 py-3 text-sm">@include('inventory.stock_adjustments._status', ['status' => $adjustment->status])</td>
                            <td class="px-4 py-3 text-sm text-right space-x-2">
                                <a href="{{ route('inventory.stock_adjustments.show', $adjustment) }}" class="text-brand-eucalyptus hover:text-brand-moss-shadow">View</a>
                                @if ($adjustment->status === 'draft')
                                    @can('stock_adjustments.create')
                                        <a href="{{ route('inventory.stock_adjustments.edit', $adjustment) }}" class="text-brand-eucalyptus hover:text-brand-moss-shadow">Edit</a>
                                    @endcan
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-sm text-gray-500">No stock adjustments found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($adjustments->hasPages())
            <div class="px-4 py-3 border-t border-gray-200">{{ $adjustments->links() }}</div>
        @endif
    </div>
</x-app-layout>
