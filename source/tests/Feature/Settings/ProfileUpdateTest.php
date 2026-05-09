<?php

use App\Models\User;

test('プロフィール画面が表示される', function () {
    // Arrange
    $user = User::factory()->create();

    // Act
    $response = $this
        ->actingAs($user)
        ->get(route('profile.edit'));

    // Assert
    $response->assertOk();
});

test('プロフィール情報を更新できる', function () {
    // Arrange
    $user = User::factory()->create();

    // Act
    $response = $this
        ->actingAs($user)
        ->patch(route('profile.update'), [
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

    // Assert
    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('profile.edit'));

    $user->refresh();

    expect($user->name)->toBe('Test User');
    expect($user->email)->toBe('test@example.com');
    expect($user->email_verified_at)->toBeNull();
});

test('メールアドレスが変わらないときメール認証ステータスも変わらない', function () {
    // Arrange
    $user = User::factory()->create();

    // Act
    $response = $this
        ->actingAs($user)
        ->patch(route('profile.update'), [
            'name' => 'Test User',
            'email' => $user->email,
        ]);

    // Assert
    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('profile.edit'));

    expect($user->refresh()->email_verified_at)->not->toBeNull();
});

test('ユーザーは自分のアカウントを削除できる', function () {
    // Arrange
    $user = User::factory()->create();

    // Act
    $response = $this
        ->actingAs($user)
        ->delete(route('profile.destroy'), [
            'password' => 'password',
        ]);

    // Assert
    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('home'));

    $this->assertGuest();
    expect($user->fresh())->toBeNull();
});

test('アカウント削除には正しいパスワードが必要', function () {
    // Arrange
    $user = User::factory()->create();

    // Act
    $response = $this
        ->actingAs($user)
        ->from(route('profile.edit'))
        ->delete(route('profile.destroy'), [
            'password' => 'wrong-password',
        ]);

    // Assert
    $response
        ->assertSessionHasErrors('password')
        ->assertRedirect(route('profile.edit'));

    expect($user->fresh())->not->toBeNull();
});
