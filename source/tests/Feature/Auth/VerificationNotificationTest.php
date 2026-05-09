<?php

use App\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Support\Facades\Notification;
use Laravel\Fortify\Features;

beforeEach(function () {
    $this->skipUnlessFortifyHas(Features::emailVerification());
});

test('メール認証通知が送信される', function () {
    // Arrange
    Notification::fake();
    $user = User::factory()->unverified()->create();

    // Act + Assert
    $this->actingAs($user)
        ->post(route('verification.send'))
        ->assertRedirect(route('home'));

    Notification::assertSentTo($user, VerifyEmail::class);
});

test('メール認証済みのときは認証通知を送信しない', function () {
    // Arrange
    Notification::fake();
    $user = User::factory()->create();

    // Act + Assert
    $this->actingAs($user)
        ->post(route('verification.send'))
        ->assertRedirect(route('dashboard', absolute: false));

    Notification::assertNothingSent();
});
