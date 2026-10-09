<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreUserRequest;
use App\Http\Requests\Admin\UpdateUserPermissionsRequest;
use App\Http\Requests\Admin\UpdateUserRequest;
use App\Models\User;
use App\Models\Warehouse;
use App\Support\PermissionMatrix;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('users.view');

        $users = User::query()
            ->with(['roles', 'defaultWarehouse'])
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->string('search');
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('admin.users.index', compact('users'));
    }

    public function create(): View
    {
        $this->authorize('users.manage');

        return view('admin.users.create', array_merge(
            ['user' => null],
            $this->formData()
        ));
    }

    public function store(StoreUserRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'password' => $validated['password'],
            'is_active' => $request->boolean('is_active', true),
            'default_warehouse_id' => $validated['default_warehouse_id'] ?? null,
        ]);

        $user->syncRoles($validated['roles'] ?? []);

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'User created successfully.');
    }

    public function show(User $user): View
    {
        $this->authorize('users.view');

        $user->load(['roles', 'permissions', 'defaultWarehouse']);

        return view('admin.users.show', compact('user'));
    }

    public function edit(User $user): View
    {
        $this->authorize('users.manage');

        $user->load(['roles', 'permissions']);

        return view('admin.users.edit', array_merge(
            ['user' => $user],
            $this->formData($user)
        ));
    }

    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $validated = $request->validated();

        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'is_active' => $request->boolean('is_active', false),
            'default_warehouse_id' => $validated['default_warehouse_id'] ?? null,
        ]);

        if (! empty($validated['password'])) {
            $user->update(['password' => $validated['password']]);
        }

        $user->syncRoles($validated['roles'] ?? []);

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'User updated successfully.');
    }

    public function editPermissions(User $user): View
    {
        $this->authorize('users.manage');

        $user->load(['roles', 'permissions', 'defaultWarehouse']);

        return view('admin.users.permissions', [
            'user' => $user,
            'modules' => PermissionMatrix::modules(),
            'actionColumns' => PermissionMatrix::actionColumns(),
            'selectedPermissions' => $user->permissions->pluck('name')->all(),
        ]);
    }

    public function updatePermissions(UpdateUserPermissionsRequest $request, User $user): RedirectResponse
    {
        $user->syncPermissions($request->validated('permissions') ?? []);

        return redirect()
            ->route('admin.users.permissions.edit', $user)
            ->with('success', 'User permissions updated successfully.');
    }

    public function destroy(User $user): RedirectResponse
    {
        $this->authorize('users.manage');

        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot delete your own account.');
        }

        $user->delete();

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'User deleted successfully.');
    }

    /**
     * @return array<string, mixed>
     */
    private function formData(?User $user = null): array
    {
        return [
            'roles' => Role::orderBy('name')->get(),
            'warehouses' => Warehouse::where('is_active', true)->orderBy('name')->get(),
            'selectedRoles' => $user?->roles->pluck('name')->all() ?? [],
        ];
    }
}
