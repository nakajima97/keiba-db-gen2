<?php

use App\Models\Horse;
use App\Models\Jockey;
use App\Models\Race;
use App\Models\RaceResultHorse;
use App\Models\User;
use App\Models\Venue;
use Inertia\Testing\AssertableInertia as Assert;

// ===== GET /horses/{horse} =====

test('unauthenticated user is redirected to login page when accessing horse show', function () {
    // Arrange
    $horse = Horse::create([
        'name' => 'テストホース',
        'birth_year' => 2020,
    ]);

    // Act
    $response = $this->get(route('horses.show', ['horse' => $horse->id]));

    // Assert
    $response->assertRedirectToRoute('login');
});

test('authenticated user can access horse show and inertia component is rendered', function () {
    // Arrange
    $user = User::factory()->create();
    $horse = Horse::create([
        'name' => 'テストホース',
        'birth_year' => 2020,
    ]);

    // Act
    $response = $this->actingAs($user)->get(route('horses.show', ['horse' => $horse->id]));

    // Assert
    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page->component('horses/show'));
});

test('horse show returns horse basic info as inertia props', function () {
    // Arrange
    $user = User::factory()->create();
    $horse = Horse::create([
        'name' => 'テストホース',
        'birth_year' => 2020,
    ]);

    // Act
    $response = $this->actingAs($user)->get(route('horses.show', ['horse' => $horse->id]));

    // Assert
    $response->assertInertia(fn (Assert $page) => $page
        ->component('horses/show')
        ->has('horse', fn (Assert $horse) => $horse
            ->where('name', 'テストホース')
            ->where('birth_year', 2020)
            ->etc()
        )
    );
});

test('horse show returns race histories with all required fields as inertia props', function () {
    // Arrange
    $user = User::factory()->create();
    $venue = Venue::firstOrCreate(['name' => '東京']);
    $horse = Horse::create([
        'name' => 'テストホース',
        'birth_year' => 2020,
    ]);
    $race = Race::create([
        'venue_id' => $venue->id,
        'race_date' => '2026-04-05',
        'race_number' => 5,
        'race_name' => '東京優駿',
    ]);

    $jockey = Jockey::create(['name' => 'テスト騎手']);
    RaceResultHorse::create([
        'race_id' => $race->id,
        'horse_id' => $horse->id,
        'jockey_id' => $jockey->id,
        'finishing_order' => 3,
        'frame_number' => 2,
        'horse_number' => 4,
        'horse_name' => 'テストホース',
        'sex_age' => '牡3',
        'weight' => '57.0',
        'jockey_name' => 'テスト騎手',
        'race_time' => '1:34.5',
        'trainer_name' => 'テスト調教師',
        'popularity' => 2,
    ]);

    // Act
    $response = $this->actingAs($user)->get(route('horses.show', ['horse' => $horse->id]));

    // Assert
    $response->assertInertia(fn (Assert $page) => $page
        ->component('horses/show')
        ->has('horse.race_histories', 1, fn (Assert $history) => $history
            ->where('race_id', $race->id)
            ->where('race_date', '2026-04-05')
            ->where('venue_name', '東京')
            ->where('race_number', 5)
            ->where('race_name', '東京優駿')
            ->where('finishing_order', 3)
            ->where('jockey_name', 'テスト騎手')
            ->where('popularity', 2)
            ->etc()
        )
    );
});

test('horse show returns empty race_histories when horse has no race history', function () {
    // Arrange
    $user = User::factory()->create();
    $horse = Horse::create([
        'name' => 'テストホース',
        'birth_year' => 2020,
    ]);

    // Act
    $response = $this->actingAs($user)->get(route('horses.show', ['horse' => $horse->id]));

    // Assert
    $response->assertInertia(fn (Assert $page) => $page
        ->component('horses/show')
        ->where('horse.race_histories', [])
    );
});

test('horse show returns race_name as null when races.race_name is null', function () {
    // Arrange
    $user = User::factory()->create();
    $venue = Venue::firstOrCreate(['name' => '東京']);
    $horse = Horse::create([
        'name' => 'テストホース',
        'birth_year' => 2020,
    ]);
    $race = Race::create([
        'venue_id' => $venue->id,
        'race_date' => '2026-04-05',
        'race_number' => 1,
        'race_name' => null,
    ]);

    $jockey = Jockey::create(['name' => 'テスト騎手']);
    RaceResultHorse::create([
        'race_id' => $race->id,
        'horse_id' => $horse->id,
        'jockey_id' => $jockey->id,
        'finishing_order' => 1,
        'frame_number' => 1,
        'horse_number' => 1,
        'horse_name' => 'テストホース',
        'sex_age' => '牡3',
        'weight' => '57.0',
        'jockey_name' => 'テスト騎手',
        'race_time' => '1:34.5',
        'trainer_name' => 'テスト調教師',
        'popularity' => 1,
    ]);

    // Act
    $response = $this->actingAs($user)->get(route('horses.show', ['horse' => $horse->id]));

    // Assert
    $response->assertInertia(fn (Assert $page) => $page
        ->component('horses/show')
        ->has('horse.race_histories', 1, fn (Assert $history) => $history
            ->where('race_name', null)
            ->etc()
        )
    );
});

test('horse show returns birth_year as null when horse has no birth_year', function () {
    // Arrange
    $user = User::factory()->create();
    $horse = Horse::create([
        'name' => 'テストホース',
        'birth_year' => null,
    ]);

    // Act
    $response = $this->actingAs($user)->get(route('horses.show', ['horse' => $horse->id]));

    // Assert
    $response->assertInertia(fn (Assert $page) => $page
        ->component('horses/show')
        ->has('horse', fn (Assert $horse) => $horse
            ->where('birth_year', null)
            ->etc()
        )
    );
});

test('horse show returns race_histories ordered by race_date desc then race_number asc', function () {
    // Arrange
    $user = User::factory()->create();
    $venue = Venue::firstOrCreate(['name' => '東京']);
    $horse = Horse::create([
        'name' => 'テストホース',
        'birth_year' => 2020,
    ]);
    $jockey = Jockey::create(['name' => 'テスト騎手']);

    $raceOlder = Race::create([
        'venue_id' => $venue->id,
        'race_date' => '2026-03-08',
        'race_number' => 1,
        'race_name' => null,
    ]);
    $raceNewerR1 = Race::create([
        'venue_id' => $venue->id,
        'race_date' => '2026-04-05',
        'race_number' => 1,
        'race_name' => null,
    ]);
    $raceNewerR5 = Race::create([
        'venue_id' => $venue->id,
        'race_date' => '2026-04-05',
        'race_number' => 5,
        'race_name' => null,
    ]);

    foreach ([$raceOlder, $raceNewerR5, $raceNewerR1] as $race) {
        RaceResultHorse::create([
            'race_id' => $race->id,
            'horse_id' => $horse->id,
            'jockey_id' => $jockey->id,
            'finishing_order' => 1,
            'frame_number' => 1,
            'horse_number' => 1,
            'horse_name' => 'テストホース',
            'sex_age' => '牡3',
            'weight' => '57.0',
            'jockey_name' => 'テスト騎手',
            'race_time' => '1:34.5',
            'trainer_name' => 'テスト調教師',
            'popularity' => 1,
        ]);
    }

    // Act
    $response = $this->actingAs($user)->get(route('horses.show', ['horse' => $horse->id]));

    // Assert
    $response->assertInertia(fn (Assert $page) => $page
        ->component('horses/show')
        ->has('horse.race_histories', 3)
        ->where('horse.race_histories.0.race_uid', $raceNewerR1->uid)
        ->where('horse.race_histories.1.race_uid', $raceNewerR5->uid)
        ->where('horse.race_histories.2.race_uid', $raceOlder->uid)
    );
});

test('horse show returns 404 for non-existent horse id', function () {
    // Arrange
    $user = User::factory()->create();

    // Act
    $response = $this->actingAs($user)->get(route('horses.show', ['horse' => 999999]));

    // Assert
    $response->assertNotFound();
});
