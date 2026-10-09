<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\PermissionGrouper;
use Illuminate\View\View;
use Spatie\Permission\Models\Permission;

class PermissionController extends Controller
{
    public function index(): View
    {
        $this->authorize('permissions.manage');

        $permissions = Permission::query()
            ->withCount('roles', 'users')
            ->orderBy('name')
            ->get();

        $permissionGroups = PermissionGrouper::group($permissions);

        return view('admin.permissions.index', compact('permissionGroups'));
    }
}
