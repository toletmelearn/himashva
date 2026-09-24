<?php

namespace Database\Factories;

use App\Models\Review;
use App\Models\ReviewMedia;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ReviewMedia>
 */
class ReviewMediaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'review_id' => Review::factory(),
            'type' => 'image',
            'file_path' => 'reviews/1/'.$this->faker->uuid().'.jpg',
            'file_name' => $this->faker->word().'.jpg',
            'file_size' => $this->faker->numberBetween(10000, 500000),
            'mime_type' => 'image/jpeg',
            'sort_order' => 0,
            'is_approved' => true,
        ];
    }
}
