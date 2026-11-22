<?php

namespace Database\Factories;

use App\Models\RaceNomination;
use App\Models\RaceNominationMark;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RaceNominationMark>
 */
class RaceNominationMarkFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'race_nomination_id' => RaceNomination::factory(),
            'user_id' => User::factory(),
            'status' => RaceNominationMark::STATUS_CHECK,
        ];
    }

    public function excluded(): static
    {
        return $this->state(fn (): array => [
            'status' => RaceNominationMark::STATUS_EXCLUDED,
        ]);
    }
}
