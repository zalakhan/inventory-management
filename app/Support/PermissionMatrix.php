<?php

namespace App\Support;

class PermissionMatrix
{
    /**
     * Module definitions for the permission matrix UI.
     * Each module maps to action keys that become permission names (e.g. products.view).
     *
     * @return array<string, array{label: string, actions: list<string>}>
     */
    public static function modules(): array
    {
        return [
            'dashboard' => ['label' => 'Dashboard', 'actions' => ['view']],
            'products' => ['label' => 'Products', 'actions' => ['view', 'create', 'edit', 'delete']],
            'categories' => ['label' => 'Categories', 'actions' => ['view', 'create', 'edit', 'delete']],
            'customers' => ['label' => 'Customers', 'actions' => ['view', 'create', 'edit', 'delete']],
            'suppliers' => ['label' => 'Suppliers', 'actions' => ['view', 'create', 'edit', 'delete']],
            'warehouses' => ['label' => 'Warehouses', 'actions' => ['view', 'create', 'edit', 'delete']],
            'stock_receiving' => ['label' => 'Stock Receiving', 'actions' => ['view', 'create', 'edit', 'post', 'cancel']],
            'inventory' => ['label' => 'Inventory', 'actions' => ['view', 'adjust']],
            'sales' => ['label' => 'Sales', 'actions' => ['view', 'create', 'edit', 'post', 'cancel']],
            'payments' => ['label' => 'Payments', 'actions' => ['view', 'create', 'post', 'cancel']],
            'sale_returns' => ['label' => 'Sale Returns', 'actions' => ['view', 'create', 'post', 'cancel']],
            'stock_adjustments' => ['label' => 'Stock Adjustments', 'actions' => ['view', 'create', 'post', 'cancel']],
            'salesmen' => ['label' => 'Salesmen', 'actions' => ['view', 'create', 'edit', 'delete']],
            'areas' => ['label' => 'Areas', 'actions' => ['view', 'create', 'edit', 'delete']],
            'reports' => ['label' => 'Reports', 'actions' => ['view', 'export']],
            'users' => ['label' => 'Users', 'actions' => ['view', 'manage']],
            'roles' => ['label' => 'Roles', 'actions' => ['manage']],
            'permissions' => ['label' => 'Permissions', 'actions' => ['manage']],
            'settings' => ['label' => 'Settings', 'actions' => ['manage']],
        ];
    }

    /**
     * Ordered action columns shown in the matrix header.
     *
     * @return list<string>
     */
    public static function actionColumns(): array
    {
        return ['view', 'create', 'edit', 'delete', 'post', 'cancel', 'adjust', 'export', 'manage'];
    }

    public static function permissionName(string $module, string $action): string
    {
        return "{$module}.{$action}";
    }

    public static function actionLabel(string $action): string
    {
        return match ($action) {
            'view' => 'View',
            'create' => 'Create',
            'edit' => 'Edit',
            'delete' => 'Delete',
            'post' => 'Post',
            'cancel' => 'Cancel',
            'adjust' => 'Adjust',
            'export' => 'Export',
            'manage' => 'Manage',
            default => ucfirst($action),
        };
    }
}
