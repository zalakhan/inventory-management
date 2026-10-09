<?php

namespace Database\Seeders;

use App\Models\Warehouse;
use Illuminate\Database\Seeder;

class WarehouseSeeder extends Seeder
{
    /**
     * Seed the default warehouse.
     */
    public function run(): void
    {
        Warehouse::firstOrCreate(
            ['code' => 'WH-01'],
            [
                'name' => 'Main Warehouse',
                'address' => null,
                'phone' => null,
                'is_default' => true,
                'is_active' => true,
            ]
        );
    }
}
