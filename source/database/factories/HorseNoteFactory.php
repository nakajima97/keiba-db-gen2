<?php

namespace Database\Factories;

use App\Models\HorseNote;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<HorseNote>
 */
class HorseNoteFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'horse_id' => null,
            'race_id' => null,
            'content' => fake()->sentence(),
        ];
    }
}
