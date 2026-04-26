<?php

namespace Database\Factories;

use App\Models\RaceMarkColumn;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RaceMarkColumn>
 */
class RaceMarkColumnFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'column_type' => 'other',
            'label' => fake()->word(),
            'display_order' => 1,
        ];
    }

    public function own(): static
    {
        return $this->state(fn (array $attributes) => [
            'column_type' => 'own',
            'label' => null,
            'display_order' => 0,
        ]);
    }

    public function other(): static
    {
        return $this->state(fn (array $attributes) => [
            'column_type' => 'other',
        ]);
    }
}
