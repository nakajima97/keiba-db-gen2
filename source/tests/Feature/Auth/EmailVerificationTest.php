<?php

use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\URL;
use Laravel\Fortify\Features;

beforeEach(function () {
    $this->skipUnlessFortifyHas(Features::emailVerification());
});

test('メール認証画面が描画される', function () {
    // Arrange
    $user = User::factory()->unverified()->create();

    // Act
    $response = $this->actingAs($user)->get(route('verification.notice'));

    // Assert
    $response->assertOk();
});

test('メールアドレスを認証できる', function () {
    // Arrange
    $user = User::factory()->unverified()->create();
    Event::fake();
    $verificationUrl = URL::temporarySignedRoute(
        'verification.verify',
        now()->addMinutes(60),
        ['id' => $user->id, 'hash' => sha1($user->email)],
    );

    // Act
    $response = $this->actingAs($user)->get($verificationUrl);

    // Assert
    Event::assertDispatched(Verified::class);
    expect($user->fresh()->hasVerifiedEmail())->toBeTrue();
    $response->assertRedirect(route('dashboard', absolute: false).'?verified=1');
});

test('ハッシュが不正なときメールアドレスは認証されない', function () {
    // Arrange
    $user = User::factory()->unverified()->create();
    Event::fake();
    $verificationUrl = URL::temporarySignedRoute(
        'verification.verify',
        now()->addMinutes(60),
        ['id' => $user->id, 'hash' => sha1('wrong-email')],
    );

    // Act
    $this->actingAs($user)->get($verificationUrl);

    // Assert
    Event::assertNotDispatched(Verified::class);
    expect($user->fresh()->hasVerifiedEmail())->toBeFalse();
});

test('ユーザーIDが不正なときメールアドレスは認証されない', function () {
    // Arrange
    $user = User::factory()->unverified()->create();
    Event::fake();
    $verificationUrl = URL::temporarySignedRoute(
        'verification.verify',
        now()->addMinutes(60),
        ['id' => 123, 'hash' => sha1($user->email)],
    );

    // Act
    $this->actingAs($user)->get($verificationUrl);

    // Assert
    Event::assertNotDispatched(Verified::class);
    expect($user->fresh()->hasVerifiedEmail())->toBeFalse();
});

test('認証済みユーザーはメール認証案内画面からダッシュボードにリダイレクトされる', function () {
    // Arrange
    $user = User::factory()->create();
    Event::fake();

    // Act
    $response = $this->actingAs($user)->get(route('verification.notice'));

    // Assert
    Event::assertNotDispatched(Verified::class);
    $response->assertRedirect(route('dashboard', absolute: false));
});

test('認証済みユーザーが認証リンクを再度開いてもイベントは再発火されずリダイレクトされる', function () {
    // Arrange
    $user = User::factory()->create();
    Event::fake();
    $verificationUrl = URL::temporarySignedRoute(
        'verification.verify',
        now()->addMinutes(60),
        ['id' => $user->id, 'hash' => sha1($user->email)],
    );

    // Act + Assert
    $this->actingAs($user)->get($verificationUrl)
        ->assertRedirect(route('dashboard', absolute: false).'?verified=1');

    Event::assertNotDispatched(Verified::class);
    expect($user->fresh()->hasVerifiedEmail())->toBeTrue();
});
