<?php

use App\Models\Horse;
use App\Models\HorseNote;
use App\Models\User;

/**
 * horse_notes destroy テスト用の horse を作成して返す
 */
function createHorseForHorseNoteDestroyTest(): Horse
{
    return Horse::create([
        'name' => 'デリートテスト用ホース'.uniqid(),
        'birth_year' => 2022,
    ]);
}

// ===== DELETE /api/horse-notes/{note} =====

test('未認証ユーザーは馬メモを削除できない', function () {
    // Arrange
    $user = User::factory()->create();
    $horse = createHorseForHorseNoteDestroyTest();
    $note = HorseNote::factory()->create([
        'user_id' => $user->id,
        'horse_id' => $horse->id,
        'race_id' => null,
        'content' => '削除対象メモ',
    ]);

    // Act
    $response = $this->deleteJson('/api/horse-notes/'.$note->id);

    // Assert
    $response->assertUnauthorized();
});

test('認証済みユーザーは自分の馬メモを削除できる', function () {
    // Arrange
    $user = User::factory()->create();
    $horse = createHorseForHorseNoteDestroyTest();
    $note = HorseNote::factory()->create([
        'user_id' => $user->id,
        'horse_id' => $horse->id,
        'race_id' => null,
        'content' => '削除対象メモ',
    ]);

    // Act
    $response = $this->actingAs($user)->deleteJson('/api/horse-notes/'.$note->id);

    // Assert
    $response->assertNoContent();
    $this->assertDatabaseMissing('horse_notes', [
        'id' => $note->id,
    ]);
});

test('他ユーザーの馬メモを削除しようとすると403が返る', function () {
    // Arrange
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $horse = createHorseForHorseNoteDestroyTest();
    $note = HorseNote::factory()->create([
        'user_id' => $otherUser->id,
        'horse_id' => $horse->id,
        'race_id' => null,
        'content' => '他人のメモ',
    ]);

    // Act
    $response = $this->actingAs($user)->deleteJson('/api/horse-notes/'.$note->id);

    // Assert
    $response->assertForbidden();
});

test('存在しない馬メモを削除しようとすると404が返る', function () {
    // Arrange
    $user = User::factory()->create();

    // Act
    $response = $this->actingAs($user)->deleteJson('/api/horse-notes/9999999');

    // Assert
    $response->assertNotFound();
});
