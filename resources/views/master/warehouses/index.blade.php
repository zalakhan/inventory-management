<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h2 class="font-semibold text-xl text-gray-800">Warehouses</h2>
                <p class="mt-1 text-sm text-gray-500">Manage branch and warehouse locations.</p>
            </div>
            @can('warehouses.create')
                <a href="{{ route('master.warehouses.create') }}" class="inline-flex items-center px-4 py-2 bg-brand-eucalyptus text-white text-sm font-medium rounded-lg hover:bg-brand-moss-shadow shrink-0">
                    Add Warehouse
                </a>
            @endcan
        </div>
    </x-slot>

    <div class="bg-white shadow-sm rounded-lg overflow-hidden">
        <form method="GET" class="p-4 border-b border-gray-200 flex gap-3">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by code or name..."
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
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Default</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse ($warehouses as $warehouse)
                        <tr>
                            <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $warehouse->code }}</td>
                            <td class="px-4 py-3 text-sm text-gray-600">{{ $warehouse->name }}</td>
                            <td class="px-4 py-3 text-sm text-gray-600">{{ $warehouse->phone ?? '—' }}</td>
                            <td class="px-4 py-3 text-sm">
                                @if ($warehouse->is_default)
                                    <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-medium bg-brand-eucalyptus text-white">Default</span>
                                @else
                                    <span class="text-gray-400">—</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-sm">
                                @if ($warehouse->is_active)
                                    <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Active</span>
                                @else
                                    <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-600">Inactive</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-sm text-right space-x-2">
                                @can('warehouses.edit')
                                    <a href="{{ route('master.warehouses.edit', $warehouse) }}" class="text-brand-eucalyptus hover:text-brand-moss-shadow">Edit</a>
                                @endcan
                                @can('warehouses.delete')
                                    <form action="{{ route('master.warehouses.destroy', $warehouse) }}" method="POST" class="inline" onsubmit="return confirm('Delete this warehouse?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-900">Delete</button>
                                    </form>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-sm text-gray-500">No warehouses found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($warehouses->hasPages())
            <div class="px-4 py-3 border-t border-gray-200">{{ $warehouses->links() }}</div>
        @endif
    </div>
</x-app-layout>
