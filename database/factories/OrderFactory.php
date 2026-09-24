<?php

namespace Database\Factories;

use App\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    public function definition(): array
    {
        $subtotal = $this->faker->randomFloat(2, 200, 2000);

        return [
            'name' => $this->faker->name(),
            'email' => $this->faker->safeEmail(),
            'phone' => '9999999999',
            'address_line_1' => $this->faker->streetAddress(),
            'city' => 'Delhi',
            'state' => 'Delhi',
            'postal_code' => '110001',
            'country' => 'India',
            'subtotal' => $subtotal,
            'discount_amount' => 0,
            'shipping_amount' => 0,
            'tax_amount' => 0,
            'total' => $subtotal,
            'payment_method' => 'cod',
            'payment_status' => 'pending',
            'order_status' => 'pending',
        ];
    }
}
