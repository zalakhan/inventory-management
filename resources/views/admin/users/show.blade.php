<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <h2 class="font-semibold text-xl text-gray-800">{{ $user->name }}</h2>
            <div class="flex flex-wrap gap-2">
                @can('users.manage')
                    <a href="{{ route('admin.users.permissions.edit', $user) }}" class="inline-flex items-center px-4 py-2 bg-amber-400 text-gray-900 text-sm font-semibold rounded-md hover:bg-amber-500">Manage Permissions</a>
                    <a href="{{ route('admin.users.edit', $user) }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-md hover:bg-indigo-700">Edit</a>
                @endcan
                <a href="{{ route('admin.users.index') }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 text-sm rounded-md text-gray-700 hover:bg-gray-50">Back</a>
            </div>
        </div>
    </x-slot>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white shadow-sm rounded-lg p-6 space-y-3">
            <h3 class="text-sm font-semibold text-gray-700 uppercase">Account</h3>
            <dl class="space-y-2 text-sm">
                <div class="flex justify-between"><dt class="text-gray-500">Email</dt><dd class="text-gray-900">{{ $user->email }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500">Phone</dt><dd class="text-gray-900">{{ $user->phone ?: '—' }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500">Status</dt>
                    <dd>@if($user->is_active)<span class="text-green-700">Active</span>@else<span class="text-red-700">Inactive</span>@endif</dd>
                </div>
                <div class="flex justify-between"><dt class="text-gray-500">Default Warehouse</dt><dd class="text-gray-900">{{ $user->defaultWarehouse?->name ?? '—' }}</dd></div>
            </dl>
        </div>

        <div class="bg-white shadow-sm rounded-lg p-6 space-y-3">
            <h3 class="text-sm font-semibold text-gray-700 uppercase">Roles</h3>
            @if ($user->roles->isNotEmpty())
                <ul class="flex flex-wrap gap-2">
                    @foreach ($user->roles as $role)
                        <li class="px-2 py-1 bg-indigo-50 text-indigo-800 text-sm rounded">{{ $role->name }}</li>
                    @endforeach
                </ul>
            @else
                <p class="text-sm text-gray-500">No roles assigned.</p>
            @endif
        </div>

        <div class="bg-white shadow-sm rounded-lg p-6 space-y-3 lg:col-span-2">
            <h3 class="text-sm font-semibold text-gray-700 uppercase">Effective Permissions</h3>
            <p class="text-sm text-gray-500">Combined from roles and direct assignments.</p>
            <div class="flex flex-wrap gap-2">
                @forelse ($user->getAllPermissions()->sortBy('name') as $permission)
                    <span class="px-2 py-1 bg-gray-100 text-gray-700 text-xs rounded">{{ $permission->name }}</span>
                @empty
                    <span class="text-sm text-gray-500">No permissions.</span>
                @endforelse
            </div>
        </div>
    </div>
</x-app-layout>
