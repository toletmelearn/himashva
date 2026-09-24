<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    public function definition(): array
    {
        $name = 'Candle '.$this->faker->unique()->word().' '.$this->faker->randomNumber(4);

        return [
            'category_id' => Category::factory(),
            'name' => $name,
            'slug' => Str::slug($name),
            'short_description' => $this->faker->sentence(),
            'description' => $this->faker->paragraph(),
            'sku' => strtoupper(Str::random(8)),
            'price' => $this->faker->randomFloat(2, 199, 1999),
            'stock' => 20,
            'is_active' => true,
        ];
    }
}
