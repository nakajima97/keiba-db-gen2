<?php

namespace Database\Factories;

use App\Models\Race;
use App\Models\RaceResultHorse;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RaceResultHorse>
 */
class RaceResultHorseFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'race_id' => Race::factory(),
            'finishing_order' => 1,
            'frame_number' => 2,
            'horse_number' => 3,
            'horse_name' => fake()->word(),
            'sex_age' => '牡3',
            'weight' => '57.0',
            'jockey_name' => fake()->name(),
            'race_time' => '1:34.5',
            'trainer_name' => fake()->name(),
            'popularity' => 1,
        ];
    }
}
