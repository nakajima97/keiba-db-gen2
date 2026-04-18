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
        ->component('pages/races/new')
        ->has('venues', 2)
    );
});
