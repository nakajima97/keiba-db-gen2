<?php

namespace Database\Factories;

use App\Models\RaceMarkColumn;
use App\Models\RaceNomination;
use App\Models\RaceNominationColumnMemo;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RaceNominationColumnMemo>
 */
class RaceNominationColumnMemoFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'race_mark_column_id' => RaceMarkColumn::factory(),
            'race_nomination_id' => RaceNomination::factory(),
            'content' => fake()->sentence(),
        ];
    }
}
