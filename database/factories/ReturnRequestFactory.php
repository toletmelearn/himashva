<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\ReturnRequest;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ReturnRequest>
 */
class ReturnRequestFactory extends Factory
{
    protected $model = ReturnRequest::class;

    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'user_id' => User::factory(),
            'reason_category' => $this->faker->randomElement(['defective', 'wrong_item', 'not_as_described', 'changed_mind', 'other']),
            'reason_text' => $this->faker->sentence(),
            'status' => 'requested',
        ];
    }
}
