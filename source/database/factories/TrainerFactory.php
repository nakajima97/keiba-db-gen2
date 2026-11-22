<?php

namespace Database\Factories;

use App\Models\Trainer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Trainer>
 */
class TrainerFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => 'trainer_'.fake()->unique()->numerify('########'),
            'affiliation' => fake()->randomElement(['美浦', '栗東']),
        ];
    }
}
