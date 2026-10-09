<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    /**
     * Seed roles and module permissions.
     */
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            'dashboard.view',
            'products.view', 'products.create', 'products.edit', 'products.delete',
            'categories.view', 'categories.create', 'categories.edit', 'categories.delete',
            'customers.view', 'customers.create', 'customers.edit', 'customers.delete',
            'suppliers.view', 'suppliers.create', 'suppliers.edit', 'suppliers.delete',
            'warehouses.view', 'warehouses.create', 'warehouses.edit', 'warehouses.delete',
            'stock_receiving.view', 'stock_receiving.create', 'stock_receiving.edit', 'stock_receiving.post', 'stock_receiving.cancel',
            'inventory.view', 'inventory.adjust',
            'sales.view', 'sales.create', 'sales.edit', 'sales.post', 'sales.cancel',
            'payments.view', 'payments.create', 'payments.post', 'payments.cancel',
            'sale_returns.view', 'sale_returns.create', 'sale_returns.post', 'sale_returns.cancel',
            'stock_adjustments.view', 'stock_adjustments.create', 'stock_adjustments.post', 'stock_adjustments.cancel',
            'salesmen.view', 'salesmen.create', 'salesmen.edit', 'salesmen.delete',
            'areas.view', 'areas.create', 'areas.edit', 'areas.delete',
            'reports.view', 'reports.export',
            'users.view', 'users.manage',
            'roles.manage',
            'permissions.manage',
            'settings.manage',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        $superAdmin = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
        $superAdmin->syncPermissions(Permission::all());

        $manager = Role::firstOrCreate(['name' => 'manager', 'guard_name' => 'web']);
        $manager->syncPermissions(array_filter($permissions, fn ($p) => ! in_array($p, [
            'users.manage', 'roles.manage', 'permissions.manage',
        ], true)));

        $salesStaff = Role::firstOrCreate(['name' => 'sales_staff', 'guard_name' => 'web']);
        $salesStaff->syncPermissions([
            'dashboard.view',
            'products.view', 'categories.view',
            'customers.view', 'customers.create', 'customers.edit',
            'sales.view', 'sales.create', 'sales.edit', 'sales.post',
            'payments.view', 'payments.create', 'payments.post',
            'sale_returns.view', 'sale_returns.create', 'sale_returns.post',
            'areas.view', 'salesmen.view',
            'reports.view',
        ]);

        $warehouseStaff = Role::firstOrCreate(['name' => 'warehouse_staff', 'guard_name' => 'web']);
        $warehouseStaff->syncPermissions([
            'dashboard.view',
            'products.view', 'categories.view',
            'warehouses.view',
            'suppliers.view',
            'stock_receiving.view', 'stock_receiving.create', 'stock_receiving.edit', 'stock_receiving.post',
            'inventory.view', 'inventory.adjust',
            'stock_adjustments.view', 'stock_adjustments.create', 'stock_adjustments.post',
            'reports.view',
        ]);
    }
}
