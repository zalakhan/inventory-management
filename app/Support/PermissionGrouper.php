<?php

namespace App\Support;

use Illuminate\Support\Collection;
use Spatie\Permission\Models\Permission;

class PermissionGrouper
{
    /**
     * Group permissions by module prefix (e.g. products.view → products).
     *
     * @param  Collection<int, Permission>  $permissions
     * @return array<string, Collection<int, Permission>>
     */
    public static function group(Collection $permissions): array
    {
        return $permissions
            ->sortBy('name')
            ->groupBy(fn (Permission $permission) => explode('.', $permission->name)[0])
            ->sortKeys()
            ->all();
    }
}
