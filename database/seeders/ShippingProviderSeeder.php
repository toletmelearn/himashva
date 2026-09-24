<?php

namespace Database\Seeders;

use App\Models\ShippingProvider;
use Illuminate\Database\Seeder;

class ShippingProviderSeeder extends Seeder
{
    public function run(): void
    {
        ShippingProvider::updateOrCreate(['name' => 'shiprocket'], [
            'display_name' => 'Shiprocket',
            'driver' => 'shiprocket',
            'credentials' => [],
            'is_active' => false,
            'is_test_mode' => true,
            'settings' => ['pickup_pincode' => '110001'],
            'supported_features' => ['tracking', 'label_generation', 'pincode_check', 'rate_calculation', 'auto_shipment'],
            'display_order' => 1,
        ]);

        ShippingProvider::updateOrCreate(['name' => 'manual'], [
            'display_name' => 'Manual Delivery',
            'driver' => 'manual',
            'credentials' => [],
            'is_active' => true,
            'is_test_mode' => false,
            'settings' => ['flat_rate' => 49, 'free_above' => 999],
            'supported_features' => ['pincode_check', 'rate_calculation'],
            'display_order' => 10,
            'description' => 'Manually tracked deliveries with flat-rate shipping.',
        ]);
    }
}
