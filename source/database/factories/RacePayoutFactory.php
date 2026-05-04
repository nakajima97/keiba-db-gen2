<?php

namespace Database\Factories;

use App\Models\Race;
use App\Models\RacePayout;
use App\Models\TicketType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RacePayout>
 */
class RacePayoutFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'race_id' => Race::factory(),
            'ticket_type_id' => TicketType::factory(),
            'payout_amount' => fake()->numberBetween(100, 100000),
            'popularity' => fake()->numberBetween(1, 18),
        ];
    }
}
