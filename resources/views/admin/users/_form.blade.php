<div class="bg-white shadow-sm rounded-lg p-6 space-y-4">
    <h3 class="text-sm font-semibold text-gray-700 uppercase tracking-wide">Account Details</h3>

    <div>
        <x-input-label for="name" value="Name" />
        <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $user?->name)" required />
        <x-input-error :messages="$errors->get('name')" class="mt-1" />
    </div>

    <div>
        <x-input-label for="email" value="Email" />
        <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email', $user?->email)" required />
        <x-input-error :messages="$errors->get('email')" class="mt-1" />
    </div>

    <div>
        <x-input-label for="phone" value="Phone" />
        <x-text-input id="phone" name="phone" type="text" class="mt-1 block w-full" :value="old('phone', $user?->phone)" />
        <x-input-error :messages="$errors->get('phone')" class="mt-1" />
    </div>

    <div>
        <x-input-label for="password" value="{{ $user ? 'New Password (leave blank to keep current)' : 'Password' }}" />
        <x-text-input id="password" name="password" type="password" class="mt-1 block w-full" :required="! $user" />
        <x-input-error :messages="$errors->get('password')" class="mt-1" />
    </div>

    <div>
        <x-input-label for="password_confirmation" value="Confirm Password" />
        <x-text-input id="password_confirmation" name="password_confirmation" type="password" class="mt-1 block w-full" />
    </div>

    <div>
        <x-input-label for="default_warehouse_id" value="Default Warehouse" />
        <select id="default_warehouse_id" name="default_warehouse_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            <option value="">— None —</option>
            @foreach ($warehouses as $warehouse)
                <option value="{{ $warehouse->id }}" @selected(old('default_warehouse_id', $user?->default_warehouse_id) == $warehouse->id)>
                    {{ $warehouse->name }}
                </option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('default_warehouse_id')" class="mt-1" />
    </div>

    <div class="flex items-center gap-2">
        <input type="hidden" name="is_active" value="0">
        <input id="is_active" name="is_active" type="checkbox" value="1"
            @checked(old('is_active', $user?->is_active ?? true))
            class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
        <x-input-label for="is_active" value="Active" class="!mb-0" />
    </div>
</div>

<div class="bg-white shadow-sm rounded-lg p-6 space-y-4">
    <h3 class="text-sm font-semibold text-gray-700 uppercase tracking-wide">Roles</h3>
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
        @foreach ($roles as $role)
            <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                <input type="checkbox" name="roles[]" value="{{ $role->name }}"
                    @checked(in_array($role->name, old('roles', $selectedRoles)))
                    class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                <span>{{ $role->name }}</span>
            </label>
        @endforeach
    </div>
    <x-input-error :messages="$errors->get('roles')" class="mt-1" />
</div>

@if ($user)
    <div class="bg-white shadow-sm rounded-lg p-6">
        <p class="text-sm text-gray-600">
            Assign roles here. Use
            <a href="{{ route('admin.users.permissions.edit', $user) }}" class="text-indigo-600 hover:text-indigo-900 font-medium">Manage Permissions</a>
            to set user-specific access.
        </p>
    </div>
@endif
