<?php

namespace Database\Factories;

use App\Models\SizeGuide;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<SizeGuide>
 */
class SizeGuideFactory extends Factory
{
    public function definition(): array
    {
        $name = $this->faker->unique()->words(2, true);

        return [
            'name' => ucwords($name),
            'slug' => Str::slug($name).'-'.Str::random(4),
            'category_id' => null,
            'type' => 'table',
            'table_data' => [
                'headers' => ['Diameter', 'Height', 'Burn Time'],
                'rows' => [['7cm', '8cm', '40hrs'], ['9cm', '10cm', '55hrs']],
            ],
            'measurement_unit' => 'cm',
            'is_active' => true,
            'description' => $this->faker->sentence(),
        ];
    }
}
