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
        'amount' => 100,
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
        'amount' => 100,
    ]);
});
