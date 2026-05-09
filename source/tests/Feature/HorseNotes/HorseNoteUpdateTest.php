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

test('未認証ユーザーは馬メモを更新できない', function () {
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

test('認証済みユーザーは自分の馬メモ本文を更新できる', function () {
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

test('他ユーザーの馬メモを更新しようとすると403が返る', function () {
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

test('存在しない馬メモを更新しようとすると404が返る', function () {
    // Arrange
    $user = User::factory()->create();

    // Act
    $response = $this->actingAs($user)->putJson('/api/horse-notes/9999999', [
        'content' => '存在しないメモの更新',
    ]);

    // Assert
    $response->assertNotFound();
});

test('更新時に本文が空のとき422が返る', function () {
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

test('更新時に本文が1000文字を超えると422が返る', function () {
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
