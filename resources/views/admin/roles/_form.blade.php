<div class="bg-white shadow-sm rounded-lg p-6 space-y-4">
    <div>
        <x-input-label for="name" value="Role Name" />
        <x-text-input id="name" name="name" type="text" class="mt-1 block w-full"
            :value="old('name', $role?->name)"
            :readonly="$isProtected ?? false"
            required />
        @if ($isProtected ?? false)
            <p class="mt-1 text-xs text-gray-500">The super_admin role name cannot be changed.</p>
        @endif
        <x-input-error :messages="$errors->get('name')" class="mt-1" />
    </div>
</div>

<div class="bg-white shadow-sm rounded-lg p-6 space-y-4">
    <h3 class="text-sm font-semibold text-gray-700 uppercase tracking-wide">Permissions</h3>
    <x-permission-checkboxes
        :permission-groups="$permissionGroups"
        :selected-permissions="old('permissions', $selectedPermissions)"
    />
    <x-input-error :messages="$errors->get('permissions')" class="mt-1" />
</div>
