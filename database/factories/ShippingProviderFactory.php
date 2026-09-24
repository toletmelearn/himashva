<?php

namespace Database\Factories;

use App\Models\ShippingProvider;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ShippingProvider>
 */
class ShippingProviderFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->word(),
            'display_name' => $this->faker->company(),
            'driver' => 'manual',
            'credentials' => [],
            'is_active' => false,
            'is_test_mode' => true,
            'settings' => ['flat_rate' => 49, 'free_above' => 999],
            'supported_features' => ['pincode_check', 'rate_calculation'],
            'display_order' => 0,
        ];
    }
}
