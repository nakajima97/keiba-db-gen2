<?php

use App\Models\Trainer;
use App\Models\TrainerNote;
use App\Models\User;

// ===== DELETE /api/trainers/{trainer}/note =====

test('未認証ユーザーが調教師メモを削除しようとするとリダイレクトされる', function () {
    // Arrange
    $trainer = Trainer::factory()->create();

    // Act
    $response = $this->delete(route('api.trainers.note.destroy', ['trainer' => $trainer->id]));

    // Assert
    $response->assertRedirect();
    $this->assertGuest();
});

test('自分の調教師メモを削除すると 204 が返りレコードが削除される', function () {
    // Arrange
    $user = User::factory()->create();
    $trainer = Trainer::factory()->create();
    TrainerNote::factory()->create([
        'user_id' => $user->id,
        'trainer_id' => $trainer->id,
    ]);

    // Act
    $response = $this->actingAs($user)->deleteJson(
        route('api.trainers.note.destroy', ['trainer' => $trainer->id]),
    );

    // Assert
    $response->assertNoContent();
    $this->assertDatabaseCount('trainer_notes', 0);
});

test('削除しても他ユーザーの同じ調教師へのメモは削除されない', function () {
    // Arrange
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $trainer = Trainer::factory()->create();
    TrainerNote::factory()->create([
        'user_id' => $user->id,
        'trainer_id' => $trainer->id,
    ]);
    TrainerNote::factory()->create([
        'user_id' => $otherUser->id,
        'trainer_id' => $trainer->id,
    ]);

    // Act
    $this->actingAs($user)->deleteJson(
        route('api.trainers.note.destroy', ['trainer' => $trainer->id]),
    );

    // Assert
    $this->assertDatabaseCount('trainer_notes', 1);
    $this->assertDatabaseHas('trainer_notes', ['user_id' => $otherUser->id]);
});

test('メモが存在しない調教師を削除しても 204 が返る', function () {
    // Arrange
    $user = User::factory()->create();
    $trainer = Trainer::factory()->create();

    // Act
    $response = $this->actingAs($user)->deleteJson(
        route('api.trainers.note.destroy', ['trainer' => $trainer->id]),
    );

    // Assert
    $response->assertNoContent();
});
