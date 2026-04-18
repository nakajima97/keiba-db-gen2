<?php

use App\Models\Race;
use App\Models\User;
use App\Models\Venue;
use Inertia\Testing\AssertableInertia as Assert;

// ===== GET /races =====

test('unauthenticated user is redirected to login page when accessing races index', function () {
    // Act
    $response = $this->get(route('races.index'));

    // Assert
    $response->assertRedirectToRoute('login');
});

test('authenticated user can access races index and inertia component is rendered', function () {
    // Arrange
    $user = User::factory()->create();

    // Act
    $response = $this->actingAs($user)->get(route('races.index'));

    // Assert
    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page->component('races/index'));
});

// ===== GET /races (フィルタリング) =====

test('races index returns all races and venues as inertia props when no filter is specified', function () {
    // Arrange
    $user = User::factory()->create();
    $venue = Venue::firstOrCreate(['name' => '東京']);
    Race::create([
        'venue_id' => $venue->id,
        'race_date' => '2026-04-05',
        'race_number' => 1,
    ]);
    Race::create([
        'venue_id' => $venue->id,
        'race_date' => '2026-04-06',
        'race_number' => 2,
    ]);

    // Act
    $response = $this->actingAs($user)->get(route('races.index'));

    // Assert
    $response->assertInertia(fn (Assert $page) => $page
        ->component('races/index')
        ->has('races', 2, fn (Assert $race) => $race
            ->hasAll(['uid', 'race_date', 'venue_name', 'race_number'])
        )
        ->has('venues', fn (Assert $venues) => $venues
            ->where('0.id', $venue->id)
            ->where('0.name', '東京')
        )
    );
});

test('races index returns only races matching race_date query parameter', function () {
    // Arrange
    $user = User::factory()->create();
    $venue = Venue::firstOrCreate(['name' => '東京']);
    Race::create([
        'venue_id' => $venue->id,
        'race_date' => '2026-04-05',
        'race_number' => 1,
    ]);
    Race::create([
        'venue_id' => $venue->id,
        'race_date' => '2026-04-06',
        'race_number' => 2,
    ]);

    // Act
    $response = $this->actingAs($user)->get(route('races.index', ['race_date' => '2026-04-05']));

    // Assert
    $response->assertInertia(fn (Assert $page) => $page
        ->component('races/index')
        ->has('races', 1, fn (Assert $race) => $race
            ->where('race_date', '2026-04-05')
            ->etc()
        )
    );
});

test('races index returns only races matching venue_id query parameter', function () {
    // Arrange
    $user = User::factory()->create();
    $tokyo = Venue::firstOrCreate(['name' => '東京']);
    $nakayama = Venue::firstOrCreate(['name' => '中山']);
    Race::create([
        'venue_id' => $tokyo->id,
        'race_date' => '2026-04-05',
        'race_number' => 1,
    ]);
    Race::create([
        'venue_id' => $nakayama->id,
        'race_date' => '2026-04-05',
        'race_number' => 1,
    ]);

    // Act
    $response = $this->actingAs($user)->get(route('races.index', ['venue_id' => $tokyo->id]));

    // Assert
    $response->assertInertia(fn (Assert $page) => $page
        ->component('races/index')
        ->has('races', 1, fn (Assert $race) => $race
            ->where('venue_name', '東京')
            ->etc()
        )
    );
});
