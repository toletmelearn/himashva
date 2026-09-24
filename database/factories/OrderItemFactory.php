<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OrderItem>
 */
class OrderItemFactory extends Factory
{
    public function definition(): array
    {
        $unitPrice = $this->faker->randomFloat(2, 199, 999);
        $quantity = 1;

        return [
            'order_id' => Order::factory(),
            'product_id' => Product::factory(),
            'variant_id' => null,
            'product_name' => $this->faker->words(3, true),
            'variant_name' => null,
            'sku' => strtoupper($this->faker->bothify('??####')),
            'unit_price' => $unitPrice,
            'quantity' => $quantity,
            'line_total' => $unitPrice * $quantity,
        ];
    }
}
