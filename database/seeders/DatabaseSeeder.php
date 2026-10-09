<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            WarehouseSeeder::class,
            RolePermissionSeeder::class,
            SettingsSeeder::class,
        ]);

        $defaultWarehouse = Warehouse::where('is_default', true)->first();

        // Migrate legacy admin email if present
        $legacyAdmin = User::where('email', 'admin@inventory.local')->first();
        if ($legacyAdmin) {
            $legacyAdmin->update(['email' => 'to.junaid@yahoo.com']);
            $admin = $legacyAdmin;
        } else {
            $admin = User::firstOrCreate(
                ['email' => 'to.junaid@yahoo.com'],
                [
                    'name' => 'Super Admin',
                    'phone' => null,
                    'password' => 'password',
                    'is_active' => true,
                    'default_warehouse_id' => $defaultWarehouse?->id,
                ]
            );
        }

        if (! $admin->hasRole('super_admin')) {
            $admin->assignRole('super_admin');
        }
    }
}
