<?php

use App\Models\Race;
use App\Models\RacePayout;
use App\Models\RacePayoutHorse;
use App\Models\TicketType;
use App\Models\User;
use App\Models\Venue;
use Database\Seeders\BuyTypeSeeder;
use Database\Seeders\TicketTypeSeeder;

beforeEach(function () {
    $this->seed(TicketTypeSeeder::class);
    $this->seed(BuyTypeSeeder::class);
});

test('馬連ながし購入で一部的中時、組合わせ数で割られず unit_stake に応じた払戻金額が記録される', function () {
    // Arrange
    $user = User::factory()->create();
    $venue = Venue::factory()->create(['name' => '東京']);
    $race = Race::factory()->create([
        'venue_id' => $venue->id,
        'race_date' => '2026-04-05',
        'race_number' => 1,
    ]);
    $umarenId = TicketType::where('name', 'umaren')->value('id');

    // 11-3 のみ的中（1,740円 / 100円）
    $payout = RacePayout::create([
        'race_id' => $race->id,
        'ticket_type_id' => $umarenId,
        'payout_amount' => 1740,
        'popularity' => 1,
    ]);
    RacePayoutHorse::create(['race_payout_id' => $payout->id, 'horse_number' => 3, 'sort_order' => 1]);
    RacePayoutHorse::create(['race_payout_id' => $payout->id, 'horse_number' => 11, 'sort_order' => 2]);

    // Act: 軸11/相手{3,7,8} = 3組合わせ・unit_stake=200円
    $this->actingAs($user)->post(route('tickets.store'), [
        'venue' => '東京',
        'race_date' => '2026-04-05',
        'race_number' => 1,
        'ticket_type' => 'umaren',
        'buy_type' => 'nagashi',
        'selections' => ['axis' => [11], 'others' => [3, 7, 8]],
        'unit_stake' => 200,
    ]);

    // Assert: 1740 × 200 / 100 = 3480（組合わせ数3で割らない）
    $this->assertDatabaseHas('ticket_purchases', [
        'user_id' => $user->id,
        'race_id' => $race->id,
        'payout_amount' => 3480,
    ]);
});
