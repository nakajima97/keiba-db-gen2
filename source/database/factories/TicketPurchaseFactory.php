<?php

namespace Database\Factories;

use App\Models\BuyType;
use App\Models\Race;
use App\Models\TicketPurchase;
use App\Models\TicketType;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TicketPurchase>
 */
class TicketPurchaseFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'race_id' => Race::factory(),
            'ticket_type_id' => TicketType::factory(),
            'buy_type_id' => BuyType::factory(),
            'selections' => ['axis' => [1], 'others' => [2, 3]],
            'unit_stake' => 1000,
            'payout_amount' => null,
        ];
    }
}
