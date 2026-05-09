<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Inertia\Testing\AssertableInertia as Assert;
use Laravel\Fortify\Features;

test('セキュリティ画面が表示される', function () {
    $this->skipUnlessFortifyHas(Features::twoFactorAuthentication());

    // Arrange
    Features::twoFactorAuthentication([
        'confirm' => true,
        'confirmPassword' => true,
    ]);

    $user = User::factory()->create();

    // Act + Assert
    $this->actingAs($user)
        ->withSession(['auth.password_confirmed_at' => time()])
        ->get(route('security.edit'))
        ->assertInertia(fn (Assert $page) => $page
            ->component('settings/security')
            ->where('canManageTwoFactor', true)
            ->where('twoFactorEnabled', false),
        );
});

test('パスワード再確認が有効のときセキュリティ画面はパスワード再確認を要求する', function () {
    $this->skipUnlessFortifyHas(Features::twoFactorAuthentication());

    // Arrange
    $user = User::factory()->create();

    Features::twoFactorAuthentication([
        'confirm' => true,
        'confirmPassword' => true,
    ]);

    // Act
    $response = $this->actingAs($user)
        ->get(route('security.edit'));

    // Assert
    $response->assertRedirect(route('password.confirm'));
});

test('パスワード再確認が無効のときセキュリティ画面はパスワード再確認を要求しない', function () {
    $this->skipUnlessFortifyHas(Features::twoFactorAuthentication());

    // Arrange
    $user = User::factory()->create();

    Features::twoFactorAuthentication([
        'confirm' => true,
        'confirmPassword' => false,
    ]);

    // Act + Assert
    $this->actingAs($user)
        ->get(route('security.edit'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('settings/security'),
        );
});

test('2段階認証機能が無効のときセキュリティ画面は2段階認証セクションなしで描画される', function () {
    $this->skipUnlessFortifyHas(Features::twoFactorAuthentication());

    // Arrange
    config(['fortify.features' => []]);
    $user = User::factory()->create();

    // Act + Assert
    $this->actingAs($user)
        ->get(route('security.edit'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('settings/security')
            ->where('canManageTwoFactor', false)
            ->missing('twoFactorEnabled')
            ->missing('requiresConfirmation'),
        );
});

test('パスワードを更新できる', function () {
    // Arrange
    $user = User::factory()->create();

    // Act
    $response = $this
        ->actingAs($user)
        ->from(route('security.edit'))
        ->put(route('user-password.update'), [
            'current_password' => 'password',
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ]);

    // Assert
    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('security.edit'));

    expect(Hash::check('new-password', $user->refresh()->password))->toBeTrue();
});

test('パスワード更新には正しい現在のパスワードが必要', function () {
    // Arrange
    $user = User::factory()->create();

    // Act
    $response = $this
        ->actingAs($user)
        ->from(route('security.edit'))
        ->put(route('user-password.update'), [
            'current_password' => 'wrong-password',
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ]);

    // Assert
    $response
        ->assertSessionHasErrors('current_password')
        ->assertRedirect(route('security.edit'));
});
