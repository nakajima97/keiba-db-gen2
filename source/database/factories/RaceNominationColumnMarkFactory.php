<?php

namespace Database\Factories;

use App\Models\RaceNomination;
use App\Models\RaceNominationColumnMark;
use App\Models\RaceNominationMarkColumn;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RaceNominationColumnMark>
 */
class RaceNominationColumnMarkFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'race_nomination_mark_column_id' => RaceNominationMarkColumn::factory(),
            'race_nomination_id' => RaceNomination::factory(),
            'mark_value' => '◎',
        ];
    }
}
