<?php

use App\Models\User;
use Illuminate\Support\Facades\RateLimiter;
use Laravel\Fortify\Features;

test('ログイン画面が描画される', function () {
    // Act
    $response = $this->get(route('login'));

    // Assert
    $response->assertOk();
});

test('ログイン画面から認証できる', function () {
    // Arrange
    $user = User::factory()->create();

    // Act
    $response = $this->post(route('login.store'), [
        'email' => $user->email,
        'password' => 'password',
    ]);

    // Assert
    $this->assertAuthenticated();
    $response->assertRedirect(route('dashboard', absolute: false));
});

test('2段階認証が有効なユーザーは2段階認証チャレンジ画面にリダイレクトされる', function () {
    $this->skipUnlessFortifyHas(Features::twoFactorAuthentication());

    // Arrange
    Features::twoFactorAuthentication([
        'confirm' => true,
        'confirmPassword' => true,
    ]);

    $user = User::factory()->create();

    $user->forceFill([
        'two_factor_secret' => encrypt('test-secret'),
        'two_factor_recovery_codes' => encrypt(json_encode(['code1', 'code2'])),
        'two_factor_confirmed_at' => now(),
    ])->save();

    // Act
    $response = $this->post(route('login'), [
        'email' => $user->email,
        'password' => 'password',
    ]);

    // Assert
    $response->assertRedirect(route('two-factor.login'));
    $response->assertSessionHas('login.id', $user->id);
    $this->assertGuest();
});

test('パスワードが誤っているとき認証されない', function () {
    // Arrange
    $user = User::factory()->create();

    // Act
    $this->post(route('login.store'), [
        'email' => $user->email,
        'password' => 'wrong-password',
    ]);

    // Assert
    $this->assertGuest();
});

test('ログアウトできる', function () {
    // Arrange
    $user = User::factory()->create();

    // Act
    $response = $this->actingAs($user)->post(route('logout'));

    // Assert
    $this->assertGuest();
    $response->assertRedirect(route('home'));
});

test('ログイン試行はレートリミットされる', function () {
    // Arrange
    $user = User::factory()->create();
    RateLimiter::increment(md5('login'.implode('|', [$user->email, '127.0.0.1'])), amount: 5);

    // Act
    $response = $this->post(route('login.store'), [
        'email' => $user->email,
        'password' => 'wrong-password',
    ]);

    // Assert
    $response->assertTooManyRequests();
});
