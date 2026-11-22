<?php

use App\Models\Trainer;
use App\Models\TrainerNote;
use App\Models\User;

// ===== PUT /api/trainers/{trainer}/note =====

test('未認証ユーザーが調教師メモを保存しようとするとリダイレクトされる', function () {
    // Arrange
    $trainer = Trainer::factory()->create();

    // Act
    $response = $this->put(route('api.trainers.note.upsert', ['trainer' => $trainer->id]), [
        'content' => 'メモ',
    ]);

    // Assert
    $response->assertRedirect();
    $this->assertGuest();
});

test('メモ未登録の調教師に保存すると 201 で新規作成される', function () {
    // Arrange
    $user = User::factory()->create();
    $trainer = Trainer::factory()->create();

    // Act
    $response = $this->actingAs($user)->putJson(
        route('api.trainers.note.upsert', ['trainer' => $trainer->id]),
        ['content' => '強気コメントは割り引いて聞く'],
    );

    // Assert
    $response->assertCreated();
    $response->assertJsonPath('data.trainer_id', $trainer->id);
    $response->assertJsonPath('data.content', '強気コメントは割り引いて聞く');
    $this->assertDatabaseHas('trainer_notes', [
        'user_id' => $user->id,
        'trainer_id' => $trainer->id,
        'content' => '強気コメントは割り引いて聞く',
    ]);
});

test('メモ登録済みの調教師に保存すると 200 で本文が更新され件数は増えない', function () {
    // Arrange
    $user = User::factory()->create();
    $trainer = Trainer::factory()->create();
    TrainerNote::factory()->create([
        'user_id' => $user->id,
        'trainer_id' => $trainer->id,
        'content' => '旧メモ',
    ]);

    // Act
    $response = $this->actingAs($user)->putJson(
        route('api.trainers.note.upsert', ['trainer' => $trainer->id]),
        ['content' => '新メモ'],
    );

    // Assert
    $response->assertOk();
    $response->assertJsonPath('data.content', '新メモ');
    $this->assertDatabaseCount('trainer_notes', 1);
    $this->assertDatabaseHas('trainer_notes', ['content' => '新メモ']);
});

test('他ユーザーが同じ調教師にメモを持っていても自分のメモとして別に作成される', function () {
    // Arrange
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $trainer = Trainer::factory()->create();
    TrainerNote::factory()->create([
        'user_id' => $otherUser->id,
        'trainer_id' => $trainer->id,
        'content' => '他人のメモ',
    ]);

    // Act
    $response = $this->actingAs($user)->putJson(
        route('api.trainers.note.upsert', ['trainer' => $trainer->id]),
        ['content' => '自分のメモ'],
    );

    // Assert
    $response->assertCreated();
    $this->assertDatabaseCount('trainer_notes', 2);
    $this->assertDatabaseHas('trainer_notes', [
        'user_id' => $otherUser->id,
        'content' => '他人のメモ',
    ]);
});

test('content が空のとき 422 が返る', function () {
    // Arrange
    $user = User::factory()->create();
    $trainer = Trainer::factory()->create();

    // Act
    $response = $this->actingAs($user)->putJson(
        route('api.trainers.note.upsert', ['trainer' => $trainer->id]),
        ['content' => ''],
    );

    // Assert
    $response->assertUnprocessable();
    $response->assertJsonValidationErrors(['content']);
});

test('content が 1000 文字を超えるとき 422 が返る', function () {
    // Arrange
    $user = User::factory()->create();
    $trainer = Trainer::factory()->create();

    // Act
    $response = $this->actingAs($user)->putJson(
        route('api.trainers.note.upsert', ['trainer' => $trainer->id]),
        ['content' => str_repeat('あ', 1001)],
    );

    // Assert
    $response->assertUnprocessable();
    $response->assertJsonValidationErrors(['content']);
});

test('存在しない調教師IDを指定すると 404 が返る', function () {
    // Arrange
    $user = User::factory()->create();

    // Act
    $response = $this->actingAs($user)->putJson(
        route('api.trainers.note.upsert', ['trainer' => 999999]),
        ['content' => 'メモ'],
    );

    // Assert
    $response->assertNotFound();
});
