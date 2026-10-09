<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h2 class="font-semibold text-xl text-gray-800">Customers</h2>
                <p class="mt-1 text-sm text-gray-500">Manage customer accounts and credit limits.</p>
            </div>
            @can('customers.create')
                <a href="{{ route('master.customers.create') }}" class="inline-flex items-center px-4 py-2 bg-brand-eucalyptus text-white text-sm font-medium rounded-lg hover:bg-brand-moss-shadow shrink-0">
                    Add Customer
                </a>
            @endcan
        </div>
    </x-slot>

    <div class="bg-white shadow-sm rounded-lg overflow-hidden">
        <form method="GET" class="p-4 border-b border-gray-200 flex gap-3">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by code, name, or phone..."
                class="flex-1 rounded-lg border-gray-300 shadow-sm focus:border-brand-eucalyptus focus:ring-brand-eucalyptus text-sm">
            <button type="submit" class="px-4 py-2 bg-brand-moss-shadow text-white text-sm rounded-lg hover:bg-brand-deep-forest">Search</button>
        </form>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Code</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Phone</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Area</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Opening Bal.</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse ($customers as $customer)
                        <tr>
                            <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $customer->code }}</td>
                            <td class="px-4 py-3 text-sm text-gray-600">{{ $customer->name }}</td>
                            <td class="px-4 py-3 text-sm text-gray-600">{{ $customer->phone ?? '—' }}</td>
                            <td class="px-4 py-3 text-sm text-gray-600">{{ $customer->area?->name ?? '—' }}</td>
                            <td class="px-4 py-3 text-sm text-gray-600 text-right">{{ number_format($customer->opening_balance, 2) }}</td>
                            <td class="px-4 py-3 text-sm">
                                @if ($customer->is_active)
                                    <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Active</span>
                                @else
                                    <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-600">Inactive</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-sm text-right space-x-2">
                                @can('customers.edit')
                                    <a href="{{ route('master.customers.edit', $customer) }}" class="text-brand-eucalyptus hover:text-brand-moss-shadow">Edit</a>
                                @endcan
                                @can('customers.delete')
                                    <form action="{{ route('master.customers.destroy', $customer) }}" method="POST" class="inline" onsubmit="return confirm('Delete this customer?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-900">Delete</button>
                                    </form>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-8 text-center text-sm text-gray-500">No customers found.</td>
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
