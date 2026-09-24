<?php

namespace Database\Factories;

use App\Models\AbandonedCart;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AbandonedCart>
 */
class AbandonedCartFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $total = $this->faker->randomFloat(2, 200, 3000);

        return [
            'session_id' => $this->faker->uuid(),
            'email' => $this->faker->safeEmail(),
            'cart_data' => [
                [
                    'product_id' => 1,
                    'variant_id' => null,
                    'product_name' => $this->faker->words(3, true),
                    'variant_name' => null,
                    'quantity' => 1,
                    'price' => $total,
                    'image_url' => null,
                ],
            ],
            'total' => $total,
            'status' => 'active',
            'reminder_count' => 0,
            'expires_at' => now()->addDays(30),
        ];
    }
}
