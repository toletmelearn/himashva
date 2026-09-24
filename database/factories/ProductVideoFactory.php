<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\ProductVideo;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProductVideo>
 */
class ProductVideoFactory extends Factory
{
    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),
            'platform' => 'youtube',
            'video_url' => 'https://www.youtube.com/watch?v='.$this->faker->regexify('[A-Za-z0-9_-]{11}'),
            'title' => $this->faker->sentence(3),
            'sort_order' => 0,
            'is_active' => true,
        ];
    }
}
