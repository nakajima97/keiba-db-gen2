<?php

use App\Models\Horse;
use App\Models\HorseNote;
use App\Models\User;

/**
 * horse_notes update テスト用の horse を作成して返す
 */
function createHorseForHorseNoteUpdateTest(): Horse
{
    return Horse::create([
        'name' => 'アップデートテスト用ホース'.uniqid(),
        'birth_year' => 2022,
    ]);
}

// ===== PUT /api/horse-notes/{note} =====

test('unauthenticated user cannot update horse note', function () {
    // Arrange
    $user = User::factory()->create();
    $horse = createHorseForHorseNoteUpdateTest();
    $note = HorseNote::factory()->create([
        'user_id' => $user->id,
        'horse_id' => $horse->id,
        'race_id' => null,
        'content' => '元のメモ',
    ]);

    // Act
    $response = $this->putJson('/api/horse-notes/'.$note->id, [
        'content' => '更新後メモ',
    ]);

    // Assert
    $response->assertUnauthorized();
});

test('authenticated user can update own horse note content', function () {
    // Arrange
    $user = User::factory()->create();
    $horse = createHorseForHorseNoteUpdateTest();
    $note = HorseNote::factory()->create([
        'user_id' => $user->id,
        'horse_id' => $horse->id,
        'race_id' => null,
        'content' => '元のメモ',
    ]);

    // Act
    $response = $this->actingAs($user)->putJson('/api/horse-notes/'.$note->id, [
        'content' => '更新後メモ',
    ]);

    // Assert
    $response->assertOk();
    $response->assertJsonPath('data.content', '更新後メモ');
    $this->assertDatabaseHas('horse_notes', [
        'id' => $note->id,
        'content' => '更新後メモ',
    ]);
});

test('updating other users horse note returns 403', function () {
    // Arrange
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $horse = createHorseForHorseNoteUpdateTest();
    $note = HorseNote::factory()->create([
        'user_id' => $otherUser->id,
        'horse_id' => $horse->id,
        'race_id' => null,
        'content' => '他人のメモ',
    ]);

    // Act
    $response = $this->actingAs($user)->putJson('/api/horse-notes/'.$note->id, [
        'content' => '書き換え',
    ]);

    // Assert
    $response->assertForbidden();
});

test('updating non-existent horse note returns 404', function () {
    // Arrange
    $user = User::factory()->create();

    // Act
    $response = $this->actingAs($user)->putJson('/api/horse-notes/9999999', [
        'content' => '存在しないメモの更新',
    ]);

    // Assert
    $response->assertNotFound();
});

test('empty content returns 422 on update', function () {
    // Arrange
    $user = User::factory()->create();
    $horse = createHorseForHorseNoteUpdateTest();
    $note = HorseNote::factory()->create([
        'user_id' => $user->id,
        'horse_id' => $horse->id,
        'race_id' => null,
        'content' => '元のメモ',
    ]);

    // Act
    $response = $this->actingAs($user)->putJson('/api/horse-notes/'.$note->id, [
        'content' => '',
    ]);

    // Assert
    $response->assertUnprocessable();
    $response->assertJsonValidationErrors(['content']);
});

test('content exceeding 1000 characters returns 422 on update', function () {
    // Arrange
    $user = User::factory()->create();
    $horse = createHorseForHorseNoteUpdateTest();
    $note = HorseNote::factory()->create([
        'user_id' => $user->id,
        'horse_id' => $horse->id,
        'race_id' => null,
        'content' => '元のメモ',
    ]);

    // Act
    $response = $this->actingAs($user)->putJson('/api/horse-notes/'.$note->id, [
        'content' => str_repeat('あ', 1001),
    ]);

    // Assert
    $response->assertUnprocessable();
    $response->assertJsonValidationErrors(['content']);
});
