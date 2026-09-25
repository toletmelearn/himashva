<?php

namespace Database\Factories;

use App\Models\Coupon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Coupon>
 */
class CouponFactory extends Factory
{
    protected $model = Coupon::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'code' => strtoupper($this->faker->unique()->bothify('SAVE##??')),
            'type' => 'percentage',
            'value' => 10,
            'min_order_amount' => null,
            'max_discount_amount' => null,
            'max_uses' => null,
            'used_count' => 0,
            'per_user_limit' => 1,
            'starts_at' => null,
            'expires_at' => null,
            'is_active' => true,
        ];
    }
}
