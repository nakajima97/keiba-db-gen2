<?php

namespace Database\Factories;

use App\Models\Horse;
use App\Models\Race;
use App\Models\RaceNomination;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RaceNomination>
 */
class RaceNominationFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'race_id' => Race::factory(),
            'horse_id' => fn (): int => (int) Horse::create([
                'name' => '登録馬'.fake()->unique()->numerify('####'),
            ])->id,
        ];
    }
}
