<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h2 class="font-semibold text-xl text-gray-800">Sale Returns</h2>
                <p class="mt-1 text-sm text-gray-500">Process customer returns — stock in and ledger credit.</p>
            </div>
            @can('sale_returns.create')
                <a href="{{ route('sales.returns.create') }}" class="inline-flex items-center px-4 py-2 bg-brand-eucalyptus text-white text-sm font-medium rounded-lg hover:bg-brand-moss-shadow shrink-0">
                    New Return
                </a>
            @endcan
        </div>
    </x-slot>

    <div class="bg-white shadow-sm rounded-lg overflow-hidden">
        <form method="GET" class="p-4 border-b border-gray-200 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Return #..."
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
            <select name="customer_id" class="rounded-lg border-gray-300 shadow-sm focus:border-brand-eucalyptus focus:ring-brand-eucalyptus text-sm">
                <option value="">All customers</option>
                @foreach ($customers as $customer)
                    <option value="{{ $customer->id }}" @selected(request('customer_id') == $customer->id)>{{ $customer->name }}</option>
                @endforeach
            </select>
            <button type="submit" class="px-4 py-2 bg-brand-moss-shadow text-white text-sm rounded-lg hover:bg-brand-deep-forest">Filter</button>
        </form>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Return #</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Customer</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Warehouse</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Total</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse ($returns as $saleReturn)
                        <tr>
                            <td class="px-4 py-3 text-sm font-medium text-gray-900">
                                <a href="{{ route('sales.returns.show', $saleReturn) }}" class="text-brand-eucalyptus hover:text-brand-moss-shadow">
                                    {{ $saleReturn->return_number }}
                                </a>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-600">{{ $saleReturn->return_date->format('d M Y') }}</td>
                            <td class="px-4 py-3 text-sm text-gray-600">{{ $saleReturn->customer?->name ?? '—' }}</td>
                            <td class="px-4 py-3 text-sm text-gray-600">{{ $saleReturn->warehouse?->name ?? '—' }}</td>
                            <td class="px-4 py-3 text-sm text-gray-600 text-right">{{ number_format($saleReturn->total_amount, 2) }}</td>
                            <td class="px-4 py-3 text-sm">@include('sales.returns._status', ['status' => $saleReturn->status])</td>
                            <td class="px-4 py-3 text-sm text-right space-x-2">
                                <a href="{{ route('sales.returns.show', $saleReturn) }}" class="text-brand-eucalyptus hover:text-brand-moss-shadow">View</a>
                                @if ($saleReturn->status === 'draft')
                                    @can('sale_returns.create')
                                        <a href="{{ route('sales.returns.edit', $saleReturn) }}" class="text-brand-eucalyptus hover:text-brand-moss-shadow">Edit</a>
                                    @endcan
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-8 text-center text-sm text-gray-500">No sale returns found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($returns->hasPages())
            <div class="px-4 py-3 border-t border-gray-200">{{ $returns->links() }}</div>
        @endif
    </div>
</x-app-layout>
