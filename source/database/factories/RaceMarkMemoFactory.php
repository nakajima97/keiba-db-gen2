<?php

namespace Database\Factories;

use App\Models\RaceMarkColumn;
use App\Models\RaceMarkMemo;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RaceMarkMemo>
 */
class RaceMarkMemoFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'race_mark_column_id' => RaceMarkColumn::factory(),
            'content' => fake()->sentence(),
        ];
    }
}
