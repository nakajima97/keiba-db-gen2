<?php

use App\Models\Race;
use App\Models\User;
use App\Models\Venue;
use Illuminate\Support\Facades\DB;
use Inertia\Testing\AssertableInertia as Assert;

// ===== GET /races/{uid} =====

test('unauthenticated user is redirected when accessing race show', function () {
    // Arrange
    $venue = Venue::firstOrCreate(['name' => '東京']);
    $race = Race::create([
        'venue_id' => $venue->id,
        'race_date' => '2026-04-05',
        'race_number' => 1,
    ]);

    // Act
    $response = $this->get(route('races.show', ['race' => $race->uid]));

    // Assert
    $response->assertRedirectToRoute('login');
});

test('authenticated user can access race show and inertia component is rendered', function () {
    // Arrange
    $user = User::factory()->create();
    $venue = Venue::firstOrCreate(['name' => '東京']);
    $race = Race::create([
        'venue_id' => $venue->id,
        'race_date' => '2026-04-05',
        'race_number' => 1,
    ]);

    // Act
    $response = $this->actingAs($user)->get(route('races.show', ['race' => $race->uid]));

    // Assert
    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page->component('races/show'));
});

test('race show returns race basic info as inertia props', function () {
    // Arrange
    $user = User::factory()->create();
    $venue = Venue::firstOrCreate(['name' => '東京']);
    $race = Race::create([
        'venue_id' => $venue->id,
        'race_date' => '2026-04-05',
        'race_number' => 3,
    ]);

    // Act
    $response = $this->actingAs($user)->get(route('races.show', ['race' => $race->uid]));

    // Assert
    $response->assertInertia(fn (Assert $page) => $page
        ->component('races/show')
        ->has('race', fn (Assert $race) => $race
            ->where('race_date', '2026-04-05')
            ->where('venue_name', '東京')
            ->where('race_number', 3)
            ->etc()
        )
    );
});

test('race show returns entries with horse and jockey info as inertia props', function () {
    // Arrange
    $user = User::factory()->create();
    $venue = Venue::firstOrCreate(['name' => '東京']);
    $race = Race::create([
        'venue_id' => $venue->id,
        'race_date' => '2026-04-05',
        'race_number' => 1,
    ]);

    $now = now();
    $horseId = DB::table('horses')->insertGetId([
        'name' => 'テストホース',
        'birth_year' => 2022,
        'created_at' => $now,
        'updated_at' => $now,
    ]);
    $jockeyId = DB::table('jockeys')->insertGetId([
        'name' => 'テスト騎手',
        'created_at' => $now,
        'updated_at' => $now,
    ]);
    DB::table('race_entries')->insert([
        'race_id' => $race->id,
        'horse_id' => $horseId,
        'jockey_id' => $jockeyId,
        'frame_number' => 1,
        'horse_number' => 1,
        'weight' => 55.0,
        'horse_weight' => 480,
        'created_at' => $now,
        'updated_at' => $now,
    ]);

    // Act
    $response = $this->actingAs($user)->get(route('races.show', ['race' => $race->uid]));

    // Assert
    $response->assertInertia(fn (Assert $page) => $page
        ->component('races/show')
        ->has('race.entries', 1, fn (Assert $entry) => $entry
            ->where('frame_number', 1)
            ->where('horse_number', 1)
            ->where('horse_name', 'テストホース')
            ->where('jockey_name', 'テスト騎手')
            ->where('weight', 480)
            ->etc()
        )
    );
});

test('race show returns 404 for non-existent uid', function () {
    // Arrange
    $user = User::factory()->create();

    // Act
    $response = $this->actingAs($user)->get(route('races.show', ['race' => 'non-existent-uid']));

    // Assert
    $response->assertNotFound();
});

test('race show returns mark_columns including own column as inertia props', function () {
    // Arrange
    $user = User::factory()->create();
    $venue = Venue::firstOrCreate(['name' => '東京']);
    $race = Race::create([
        'venue_id' => $venue->id,
        'race_date' => '2026-04-05',
        'race_number' => 1,
    ]);

    // Act
    $response = $this->actingAs($user)->get(route('races.show', ['race' => $race->uid]));

    // Assert
    $response->assertInertia(fn (Assert $page) => $page
        ->component('races/show')
        ->has('race.mark_columns')
        ->where('race.mark_columns', function ($columns) {
            return collect($columns)->pluck('type')->contains('own');
        })
    );
});

test('race show returns marks as inertia props', function () {
    // Arrange
    $user = User::factory()->create();
    $venue = Venue::firstOrCreate(['name' => '東京']);
    $race = Race::create([
        'venue_id' => $venue->id,
        'race_date' => '2026-04-05',
        'race_number' => 1,
    ]);

    // Act
    $response = $this->actingAs($user)->get(route('races.show', ['race' => $race->uid]));

    // Assert
    $response->assertInertia(fn (Assert $page) => $page
        ->component('races/show')
        ->has('race.marks')
        ->where('race.marks', fn ($marks) => is_iterable($marks))
    );
});
