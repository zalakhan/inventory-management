<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h2 class="font-semibold text-xl text-gray-800">Salesmen</h2>
                <p class="mt-1 text-sm text-gray-500">Manage field sales staff and route assignments.</p>
            </div>
            @can('salesmen.create')
                <a href="{{ route('master.salesmen.create') }}" class="inline-flex items-center px-4 py-2 bg-brand-eucalyptus text-white text-sm font-medium rounded-lg hover:bg-brand-moss-shadow shrink-0">
                    Add Salesman
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
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Area</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Linked User</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse ($salesmen as $salesman)
                        <tr>
                            <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $salesman->code }}</td>
                            <td class="px-4 py-3 text-sm text-gray-600">{{ $salesman->name }}</td>
                            <td class="px-4 py-3 text-sm text-gray-600">{{ $salesman->area?->name ?? '—' }}</td>
                            <td class="px-4 py-3 text-sm text-gray-600">{{ $salesman->user?->name ?? '—' }}</td>
                            <td class="px-4 py-3 text-sm">
                                @if ($salesman->is_active)
                                    <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Active</span>
                                @else
                                    <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-600">Inactive</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-sm text-right space-x-2">
                                @can('salesmen.edit')
                                    <a href="{{ route('master.salesmen.edit', $salesman) }}" class="text-brand-eucalyptus hover:text-brand-moss-shadow">Edit</a>
                                @endcan
                                @can('salesmen.delete')
                                    <form action="{{ route('master.salesmen.destroy', $salesman) }}" method="POST" class="inline" onsubmit="return confirm('Delete this salesman?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-900">Delete</button>
                                    </form>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-sm text-gray-500">No salesmen found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($salesmen->hasPages())
            <div class="px-4 py-3 border-t border-gray-200">{{ $salesmen->links() }}</div>
        @endif
    </div>
</x-app-layout>
