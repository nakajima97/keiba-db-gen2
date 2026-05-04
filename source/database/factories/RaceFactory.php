<?php

namespace Database\Factories;

use App\Models\Race;
use App\Models\Venue;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Race>
 */
class RaceFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'venue_id' => Venue::factory(),
            'race_date' => '2026-04-05',
            'race_number' => fake()->numberBetween(1, 12),
            'race_name' => null,
        ];
    }
}
