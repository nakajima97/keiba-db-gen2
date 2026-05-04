<?php

namespace Database\Factories;

use App\Models\TicketType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TicketType>
 */
class TicketTypeFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => 'type_'.fake()->unique()->numerify('########'),
            'label' => fake()->word(),
            'sort_order' => fake()->numberBetween(1, 100),
        ];
    }
}
