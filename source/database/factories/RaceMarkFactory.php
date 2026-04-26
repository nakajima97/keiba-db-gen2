<?php

namespace Database\Factories;

use App\Models\RaceMark;
use App\Models\RaceMarkColumn;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RaceMark>
 */
class RaceMarkFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'race_mark_column_id' => RaceMarkColumn::factory(),
            'mark_value' => '◎',
        ];
    }
}
