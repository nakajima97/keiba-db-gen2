<?php

use App\Models\User;
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
