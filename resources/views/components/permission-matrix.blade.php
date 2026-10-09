@props(['modules', 'actionColumns', 'selectedPermissions' => [], 'name' => 'permissions'])

<div class="overflow-x-auto border border-gray-200 rounded-lg">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider w-48">Module</th>
                @foreach ($actionColumns as $action)
                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">{{ \App\Support\PermissionMatrix::actionLabel($action) }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            @foreach ($modules as $moduleKey => $module)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $module['label'] }}</td>
                    @foreach ($actionColumns as $action)
                        @php
                            $permissionName = \App\Support\PermissionMatrix::permissionName($moduleKey, $action);
                            $hasAction = in_array($action, $module['actions'], true);
                        @endphp
                        <td class="px-4 py-3 text-center">
                            @if ($hasAction)
                                <input
                                    type="checkbox"
                                    name="{{ $name }}[]"
                                    value="{{ $permissionName }}"
                                    @checked(in_array($permissionName, $selectedPermissions))
                                    class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500 h-4 w-4"
                                >
                            @else
                                <span class="text-gray-300">—</span>
                            @endif
                        </td>
                    @endforeach
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
