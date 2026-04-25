<?php

use App\Models\Race;
use App\Models\User;
use App\Models\Venue;
use Inertia\Testing\AssertableInertia as Assert;

// ===== GET /races/{uid}/entries/new =====

test('unauthenticated user is redirected when accessing race entries create', function () {
    // Arrange
    $venue = Venue::firstOrCreate(['name' => '東京']);
    $race = Race::create([
        'venue_id' => $venue->id,
        'race_date' => '2026-04-18',
        'race_number' => 1,
    ]);

    // Act
    $response = $this->get(route('races.entries.create', ['race' => $race->uid]));

    // Assert
    $response->assertRedirectToRoute('login');
});

test('authenticated user can access race entries create and inertia component is rendered with race info', function () {
    // Arrange
    $user = User::factory()->create();
    $venue = Venue::firstOrCreate(['name' => '東京']);
    $race = Race::create([
        'venue_id' => $venue->id,
        'race_date' => '2026-04-18',
        'race_number' => 3,
    ]);

    // Act
    $response = $this->actingAs($user)->get(route('races.entries.create', ['race' => $race->uid]));

    // Assert
    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('races/entries/new')
        ->has('race_info', fn (Assert $raceInfo) => $raceInfo
            ->where('race_date', '2026-04-18')
            ->where('venue_name', '東京')
            ->where('race_number', 3)
            ->etc()
        )
    );
});
