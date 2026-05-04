<?php

use Illuminate\Support\Facades\DB;

test('race_payouts.popularity に最大値 65535 を保存できる', function () {
    // Arrange
    $now = now();

    $venueId = DB::table('venues')->insertGetId([
        'name' => '東京',
        'created_at' => $now,
        'updated_at' => $now,
    ]);

    $raceId = DB::table('races')->insertGetId([
        'uid' => 'test-uid-'.uniqid(),
        'venue_id' => $venueId,
        'race_date' => '2026-04-05',
        'race_number' => 1,
        'created_at' => $now,
        'updated_at' => $now,
    ]);

    $ticketTypeId = DB::table('ticket_types')->insertGetId([
        'name' => 'tansho',
        'label' => '単勝',
        'sort_order' => 1,
        'created_at' => $now,
        'updated_at' => $now,
    ]);

    // Act
    DB::table('race_payouts')->insert([
        'race_id' => $raceId,
        'ticket_type_id' => $ticketTypeId,
        'payout_amount' => 99999,
        'popularity' => 65535,
        'created_at' => $now,
        'updated_at' => $now,
    ]);

    // Assert
    $this->assertDatabaseHas('race_payouts', [
        'race_id' => $raceId,
        'ticket_type_id' => $ticketTypeId,
        'popularity' => 65535,
    ]);
});
