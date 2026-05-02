<?php

use App\Models\User;
use Illuminate\Support\Facades\DB;

test('authenticated user can purchase a ticket', function () {
    // Arrange
    $user = User::factory()->create();

    $now = now();

    DB::table('venues')->insert([
        'name' => '東京',
        'created_at' => $now,
        'updated_at' => $now,
    ]);

    $ticketType = DB::table('ticket_types')->insertGetId([
        'name' => 'umaren',
        'label' => '馬連',
        'sort_order' => 4,
        'created_at' => $now,
        'updated_at' => $now,
    ]);

    $buyType = DB::table('buy_types')->insertGetId([
        'name' => 'nagashi',
        'label' => '流し',
        'sort_order' => 2,
        'created_at' => $now,
        'updated_at' => $now,
    ]);

    // Act
    $response = $this->actingAs($user)->post(route('tickets.store'), [
        'venue' => '東京',
        'race_date' => '2026-04-05',
        'race_number' => 1,
        'ticket_type' => 'umaren',
        'buy_type' => 'nagashi',
        'selections' => ['axis' => [3], 'others' => [1, 5, 7]],
        'unit_stake' => 100,
    ]);

    // Assert
    $response->assertRedirect(route('tickets.new'));

    $race = DB::table('races')->where('race_date', '2026-04-05')->where('race_number', 1)->first();
    expect($race)->not->toBeNull();

    $this->assertDatabaseHas('ticket_purchases', [
        'user_id' => $user->id,
        'race_id' => $race->id,
        'ticket_type_id' => $ticketType,
        'buy_type_id' => $buyType,
        'unit_stake' => 100,
    ]);
});

test('単複で馬券購入を記録できる', function () {
    // Arrange
    $user = User::factory()->create();

    $now = now();

    DB::table('venues')->insert([
        'name' => '東京',
        'created_at' => $now,
        'updated_at' => $now,
    ]);

    $ticketType = DB::table('ticket_types')->insertGetId([
        'name' => 'tanpuku',
        'label' => '単複',
        'sort_order' => 9,
        'created_at' => $now,
        'updated_at' => $now,
    ]);

    $buyType = DB::table('buy_types')->insertGetId([
        'name' => 'single',
        'label' => '通常',
        'sort_order' => 1,
        'created_at' => $now,
        'updated_at' => $now,
    ]);

    // Act
    $response = $this->actingAs($user)->post(route('tickets.store'), [
        'venue' => '東京',
        'race_date' => '2026-04-11',
        'race_number' => 1,
        'ticket_type' => 'tanpuku',
        'buy_type' => 'single',
        'selections' => ['horses' => [5]],
        'unit_stake' => 200,
    ]);

    // Assert
    $response->assertRedirect(route('tickets.new'));

    $race = DB::table('races')->where('race_date', '2026-04-11')->where('race_number', 1)->first();
    expect($race)->not->toBeNull();

    $this->assertDatabaseHas('ticket_purchases', [
        'user_id' => $user->id,
        'race_id' => $race->id,
        'ticket_type_id' => $ticketType,
        'buy_type_id' => $buyType,
        'unit_stake' => 200,
    ]);
});

test('レース結果登録済みの場合、馬券購入時に payout_amount が計算される', function () {
    // Arrange
    $user = User::factory()->create();

    $now = now();

    $venueId = DB::table('venues')->insertGetId([
        'name' => '東京',
        'created_at' => $now,
        'updated_at' => $now,
    ]);

    $tanshoTypeId = DB::table('ticket_types')->insertGetId([
        'name' => 'tansho',
        'label' => '単勝',
        'sort_order' => 1,
        'created_at' => $now,
        'updated_at' => $now,
    ]);

    DB::table('buy_types')->insert([
        'name' => 'single',
        'label' => '通常',
        'sort_order' => 1,
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

    // race_payouts（単勝3番 610円）を登録済みにする
    $payoutId = DB::table('race_payouts')->insertGetId([
        'race_id' => $raceId,
        'ticket_type_id' => $tanshoTypeId,
        'payout_amount' => 610,
        'popularity' => 2,
        'created_at' => $now,
        'updated_at' => $now,
    ]);

    DB::table('race_payout_horses')->insert([
        'race_payout_id' => $payoutId,
        'horse_number' => 3,
        'sort_order' => 1,
        'created_at' => $now,
        'updated_at' => $now,
    ]);

    // Act
    $this->actingAs($user)->post(route('tickets.store'), [
        'venue' => '東京',
        'race_date' => '2026-04-05',
        'race_number' => 1,
        'ticket_type' => 'tansho',
        'buy_type' => 'single',
        'selections' => ['horses' => [3]],
        'unit_stake' => 100,
    ]);

    // Assert
    $this->assertDatabaseHas('ticket_purchases', [
        'user_id' => $user->id,
        'race_id' => $raceId,
        'payout_amount' => 610,
    ]);
});
