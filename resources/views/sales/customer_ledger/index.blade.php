<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="font-semibold text-xl text-gray-800">Customer Ledger</h2>
            <p class="mt-1 text-sm text-gray-500">View outstanding balances and open customer statements.</p>
        </div>
    </x-slot>

    <div class="bg-white shadow-sm rounded-lg overflow-hidden">
        <form method="GET" class="p-4 border-b border-gray-200 flex flex-col sm:flex-row gap-3">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by code, name, or phone..."
                class="flex-1 rounded-lg border-gray-300 shadow-sm focus:border-brand-eucalyptus focus:ring-brand-eucalyptus text-sm">
            <button type="submit" class="px-4 py-2 bg-brand-moss-shadow text-white text-sm rounded-lg hover:bg-brand-deep-forest">Search</button>
        </form>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Code</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Customer</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Area</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Outstanding</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse ($customers as $customer)
                        <tr>
                            <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $customer->code }}</td>
                            <td class="px-4 py-3 text-sm text-gray-600">{{ $customer->name }}</td>
                            <td class="px-4 py-3 text-sm text-gray-600">{{ $customer->area?->name ?? '—' }}</td>
                            <td class="px-4 py-3 text-sm text-right font-medium {{ bccomp($balances[$customer->id] ?? '0', '0', 2) > 0 ? 'text-red-600' : 'text-gray-900' }}">
                                {{ number_format($balances[$customer->id] ?? 0, 2) }}
                            </td>
                            <td class="px-4 py-3 text-sm text-right">
                                <a href="{{ route('sales.customer_ledger.show', $customer) }}" class="text-brand-eucalyptus hover:text-brand-moss-shadow">
                                    View Statement
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-8 text-center text-sm text-gray-500">No customers found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($customers->hasPages())
            <div class="px-4 py-3 border-t border-gray-200">{{ $customers->links() }}</div>
        @endif
    </div>
</x-app-layout>
