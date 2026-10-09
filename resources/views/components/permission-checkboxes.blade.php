@props(['permissionGroups', 'selectedPermissions' => [], 'name' => 'permissions'])

<div class="space-y-4">
    @foreach ($permissionGroups as $module => $permissions)
        <div class="border border-gray-200 rounded-lg overflow-hidden">
            <div class="bg-gray-50 px-4 py-2 border-b border-gray-200">
                <span class="text-sm font-semibold text-gray-700 capitalize">{{ str_replace('_', ' ', $module) }}</span>
            </div>
            <div class="p-4 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2">
                @foreach ($permissions as $permission)
                    <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                        <input
                            type="checkbox"
                            name="{{ $name }}[]"
                            value="{{ $permission->name }}"
                            @checked(in_array($permission->name, $selectedPermissions))
                            class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                        >
                        <span>{{ $permission->name }}</span>
                    </label>
                @endforeach
            </div>
        </div>
    @endforeach
</div>
