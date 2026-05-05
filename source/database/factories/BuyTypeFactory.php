<?php

namespace Database\Factories;

use App\Models\BuyType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BuyType>
 */
class BuyTypeFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => 'buy_type_'.fake()->unique()->numerify('########'),
            'label' => fake()->word(),
            'sort_order' => fake()->numberBetween(1, 100),
        ];
    }
}
