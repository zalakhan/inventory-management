<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800">Permissions</h2>
    </x-slot>

    <p class="mb-4 text-sm text-gray-600">
        Permissions are assigned through roles or direct user assignments. This page shows all available permissions grouped by module.
    </p>

    <div class="space-y-4">
        @foreach ($permissionGroups as $module => $permissions)
            <div class="bg-white shadow-sm rounded-lg overflow-hidden">
                <div class="bg-gray-50 px-4 py-3 border-b border-gray-200">
                    <h3 class="text-sm font-semibold text-gray-800 capitalize">{{ str_replace('_', ' ', $module) }}</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Permission</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Roles</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Direct Users</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @foreach ($permissions as $permission)
                                <tr>
                                    <td class="px-4 py-2 text-sm text-gray-900 font-mono">{{ $permission->name }}</td>
                                    <td class="px-4 py-2 text-sm text-gray-600">{{ $permission->roles_count }}</td>
                                    <td class="px-4 py-2 text-sm text-gray-600">{{ $permission->users_count }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endforeach
    </div>
</x-app-layout>
