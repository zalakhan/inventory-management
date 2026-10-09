<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h2 class="font-semibold text-xl text-gray-800">Sales Register</h2>
                <p class="mt-1 text-sm text-gray-500">Posted sales invoices.</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('reports.index') }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 text-sm rounded-lg hover:bg-gray-50">All Reports</a>
                @include('reports._export-button')
            </div>
        </div>
    </x-slot>

    <div class="bg-white shadow-sm rounded-lg overflow-hidden">
        <form method="GET" class="p-4 border-b border-gray-200 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
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
            <select name="customer_id" class="rounded-lg border-gray-300 shadow-sm focus:border-brand-eucalyptus focus:ring-brand-eucalyptus text-sm">
                <option value="">All customers</option>
                @foreach ($customers as $customer)
                    <option value="{{ $customer->id }}" @selected(request('customer_id') == $customer->id)>{{ $customer->code }} — {{ $customer->name }}</option>
                @endforeach
            </select>
            <button type="submit" class="px-4 py-2 bg-brand-moss-shadow text-white text-sm rounded-lg hover:bg-brand-deep-forest">Filter</button>
        </form>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Invoice</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Customer</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Warehouse</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Total</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Paid</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Outstanding</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse ($sales as $sale)
                        <tr>
                            <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $sale->invoice_number }}</td>
                            <td class="px-4 py-3 text-sm text-gray-600">{{ $sale->sale_date?->format('M d, Y') }}</td>
                            <td class="px-4 py-3 text-sm text-gray-600">{{ $sale->customer?->name ?? '—' }}</td>
                            <td class="px-4 py-3 text-sm text-gray-600">{{ $sale->warehouse?->name ?? '—' }}</td>
                            <td class="px-4 py-3 text-sm text-gray-900 text-right">{{ number_format($sale->total_amount, 2) }}</td>
                            <td class="px-4 py-3 text-sm text-gray-600 text-right">{{ number_format($sale->amount_paid, 2) }}</td>
                            <td class="px-4 py-3 text-sm text-gray-900 text-right">{{ number_format($sale->outstanding_amount, 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-8 text-center text-sm text-gray-500">No posted sales found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($sales->hasPages())
            <div class="px-4 py-3 border-t border-gray-200">{{ $sales->links() }}</div>
        @endif
    </div>
</x-app-layout>
