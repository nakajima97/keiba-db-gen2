<?php

use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('パスワード再確認画面が描画される', function () {
    // Arrange
    $user = User::factory()->create();

    // Act
    $response = $this->actingAs($user)->get(route('password.confirm'));

    // Assert
    $response->assertOk();

    $response->assertInertia(fn (Assert $page) => $page
        ->component('auth/confirm-password'),
    );
});

test('パスワード再確認には認証が必要', function () {
    // Act
    $response = $this->get(route('password.confirm'));

    // Assert
    $response->assertRedirect(route('login'));
});
