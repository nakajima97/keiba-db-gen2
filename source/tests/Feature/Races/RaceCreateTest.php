<?php

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Inertia\Testing\AssertableInertia as Assert;

// ===== GET /races/new =====

test('unauthenticated user is redirected to login page when accessing race create page', function () {
    // Act
    $response = $this->get(route('races.create'));

    // Assert
    $response->assertRedirectToRoute('login');
});

test('authenticated user can access race create page with venues prop', function () {
    // Arrange
    $user = User::factory()->create();
    $now = now();
    DB::table('venues')->insert([
        ['name' => '東京', 'created_at' => $now, 'updated_at' => $now],
        ['name' => '阪神', 'created_at' => $now, 'updated_at' => $now],
    ]);

    // Act
    $response = $this->actingAs($user)->get(route('races.create'));

    // Assert
    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('races/new')
        ->has('venues', 2)
    );
});

test('race_date query parameter takes precedence over session last_race_date', function () {
    // Arrange
    $user = User::factory()->create();

    // Act
    $response = $this->actingAs($user)
        ->withSession(['last_race_date' => '2026-01-01'])
        ->get(route('races.create', ['race_date' => '2026-05-01']));

    // Assert
    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->where('last_race_date', '2026-05-01')
    );
});

test('session last_race_date is used when race_date query parameter is absent', function () {
    // Arrange
    $user = User::factory()->create();

    // Act
    $response = $this->actingAs($user)
        ->withSession(['last_race_date' => '2026-01-01'])
        ->get(route('races.create'));

    // Assert
    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->where('last_race_date', '2026-01-01')
    );
});

test('invalid race_date query parameter is ignored and falls back to session last_race_date', function () {
    // Arrange
    $user = User::factory()->create();

    // Act
    $response = $this->actingAs($user)
        ->withSession(['last_race_date' => '2026-01-01'])
        ->get(route('races.create', ['race_date' => 'invalid-date']));

    // Assert
    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->where('last_race_date', '2026-01-01')
    );
});
