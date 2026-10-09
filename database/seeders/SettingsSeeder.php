<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    /**
     * Seed default system settings.
     */
    public function run(): void
    {
        $settings = [
            ['group' => 'general', 'key' => 'company_name', 'value' => 'Prime Distributions', 'type' => 'string'],
            ['group' => 'general', 'key' => 'company_phone', 'value' => null, 'type' => 'string'],
            ['group' => 'general', 'key' => 'company_address', 'value' => null, 'type' => 'string'],
            ['group' => 'numbering', 'key' => 'sales.invoice_prefix', 'value' => 'INV-', 'type' => 'string'],
            ['group' => 'numbering', 'key' => 'sales.invoice_next', 'value' => '1', 'type' => 'integer'],
            ['group' => 'numbering', 'key' => 'stock_receiving.receipt_prefix', 'value' => 'GRN-', 'type' => 'string'],
            ['group' => 'numbering', 'key' => 'stock_receiving.receipt_next', 'value' => '1', 'type' => 'integer'],
            ['group' => 'numbering', 'key' => 'payments.receipt_prefix', 'value' => 'RCP-', 'type' => 'string'],
            ['group' => 'numbering', 'key' => 'payments.receipt_next', 'value' => '1', 'type' => 'integer'],
            ['group' => 'numbering', 'key' => 'sale_returns.return_prefix', 'value' => 'SRN-', 'type' => 'string'],
            ['group' => 'numbering', 'key' => 'sale_returns.return_next', 'value' => '1', 'type' => 'integer'],
            ['group' => 'numbering', 'key' => 'stock_adjustments.adjustment_prefix', 'value' => 'ADJ-', 'type' => 'string'],
            ['group' => 'numbering', 'key' => 'stock_adjustments.adjustment_next', 'value' => '1', 'type' => 'integer'],
        ];

        foreach ($settings as $setting) {
            Setting::updateOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }
    }
}
