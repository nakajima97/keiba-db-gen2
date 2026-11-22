<?php

namespace Database\Factories;

use App\Models\Trainer;
use App\Models\TrainerNote;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TrainerNote>
 */
class TrainerNoteFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'trainer_id' => Trainer::factory(),
            'content' => fake()->sentence(),
        ];
    }
}
