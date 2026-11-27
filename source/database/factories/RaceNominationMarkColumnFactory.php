<?php

namespace Database\Factories;

use App\Models\Race;
use App\Models\RaceNominationMarkColumn;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RaceNominationMarkColumn>
 */
class RaceNominationMarkColumnFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'race_id' => Race::factory(),
            'user_id' => User::factory(),
            'label' => fake()->word(),
            'display_order' => 1,
        ];
    }
}
