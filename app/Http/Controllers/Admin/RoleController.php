<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreRoleRequest;
use App\Http\Requests\Admin\UpdateRoleRequest;
use App\Support\PermissionGrouper;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    /** Roles that cannot be renamed or deleted. */
    private const protectedRoles = ['super_admin'];

    public function index(): View
    {
        $this->authorize('roles.manage');

        $roles = Role::query()
            ->withCount('permissions', 'users')
            ->orderBy('name')
            ->get();

        return view('admin.roles.index', compact('roles'));
    }

    public function create(): View
    {
        $this->authorize('roles.manage');

        return view('admin.roles.create', [
            'role' => null,
            'isProtected' => false,
            'permissionGroups' => PermissionGrouper::group(Permission::orderBy('name')->get()),
            'selectedPermissions' => [],
        ]);
    }

    public function store(StoreRoleRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $role = Role::create(['name' => $validated['name'], 'guard_name' => 'web']);
        $role->syncPermissions($validated['permissions'] ?? []);

        return redirect()
            ->route('admin.roles.index')
            ->with('success', 'Role created successfully.');
    }

    public function edit(Role $role): View
    {
        $this->authorize('roles.manage');

        $role->load('permissions');

        return view('admin.roles.edit', [
            'role' => $role,
            'permissionGroups' => PermissionGrouper::group(Permission::orderBy('name')->get()),
            'selectedPermissions' => $role->permissions->pluck('name')->all(),
            'isProtected' => in_array($role->name, self::protectedRoles, true),
        ]);
    }

    public function update(UpdateRoleRequest $request, Role $role): RedirectResponse
    {
        $validated = $request->validated();

        if (! in_array($role->name, self::protectedRoles, true)) {
            $role->update(['name' => $validated['name']]);
        }

        $role->syncPermissions($validated['permissions'] ?? []);

        return redirect()
            ->route('admin.roles.index')
            ->with('success', 'Role updated successfully.');
    }

    public function destroy(Role $role): RedirectResponse
    {
        $this->authorize('roles.manage');

        if (in_array($role->name, self::protectedRoles, true)) {
            return back()->with('error', 'This role cannot be deleted.');
        }

        if ($role->users()->count() > 0) {
            return back()->with('error', 'Remove all users from this role before deleting it.');
        }

        $role->delete();

        return redirect()
            ->route('admin.roles.index')
            ->with('success', 'Role deleted successfully.');
    }
}
