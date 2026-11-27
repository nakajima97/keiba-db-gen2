<?php

use App\Models\RaceMarkColumn;
use App\Models\RaceNomination;
use App\Models\RaceNominationColumnMark;
use App\Models\RaceNominationColumnMemo;
use App\Models\RaceNominationMarkColumn;
use App\Models\User;

/**
 * 登録馬の印メモの URL を返す
 */
function nominationColumnMemoUrl(RaceNomination $nomination, int $columnId): string
{
    return '/api/races/'.$nomination->race->uid.'/nomination-mark-columns/'.$columnId.'/nominations/'.$nomination->id.'/memo';
}

// ===== PUT /api/races/{uid}/nomination-mark-columns/{column_id}/nominations/{nomination_id}/memo =====

test('印がない登録馬にも印メモを新規作成でき201が返る', function () {
    // Arrange
    $user = User::factory()->create();
    $nomination = RaceNomination::factory()->create();
    $column = RaceNominationMarkColumn::factory()->create([
        'race_id' => $nomination->race_id,
        'user_id' => $user->id,
    ]);

    // Act
    $response = $this->actingAs($user)->putJson(nominationColumnMemoUrl($nomination, $column->id), ['content' => '登録段階から注目']);

    // Assert
    $response->assertCreated();
    $response->assertJsonPath('data.race_nomination_mark_column_id', $column->id);
    $response->assertJsonPath('data.race_nomination_id', $nomination->id);
    $response->assertJsonPath('data.content', '登録段階から注目');
    $this->assertDatabaseHas('race_nomination_column_memos', [
        'race_nomination_mark_column_id' => $column->id,
        'race_nomination_id' => $nomination->id,
        'content' => '登録段階から注目',
    ]);
});

test('既存の印メモを1000文字で更新でき200が返る', function () {
    // Arrange
    $user = User::factory()->create();
    $nomination = RaceNomination::factory()->create();
    $column = RaceNominationMarkColumn::factory()->create([
        'race_id' => $nomination->race_id,
        'user_id' => $user->id,
    ]);
    RaceNominationColumnMemo::factory()->create([
        'race_nomination_mark_column_id' => $column->id,
        'race_nomination_id' => $nomination->id,
        'content' => '旧メモ',
    ]);
    $content = str_repeat('あ', 1000);

    // Act
    $response = $this->actingAs($user)->putJson(nominationColumnMemoUrl($nomination, $column->id), ['content' => $content]);

    // Assert
    $response->assertOk();
    $response->assertJsonPath('data.content', $content);
    $this->assertDatabaseCount('race_nomination_column_memos', 1);
});

test('印メモが1001文字の場合は422が返る', function () {
    // Arrange
    $user = User::factory()->create();
    $nomination = RaceNomination::factory()->create();
    $column = RaceNominationMarkColumn::factory()->create([
        'race_id' => $nomination->race_id,
        'user_id' => $user->id,
    ]);

    // Act
    $response = $this->actingAs($user)->putJson(nominationColumnMemoUrl($nomination, $column->id), ['content' => str_repeat('あ', 1001)]);

    // Assert
    $response->assertUnprocessable();
    $response->assertJsonValidationErrors(['content']);
    $this->assertDatabaseCount('race_nomination_column_memos', 0);
});

test('出馬表の印列IDで登録馬の印メモを作成しようとすると404が返る', function () {
    // Arrange
    $user = User::factory()->create();
    $nomination = RaceNomination::factory()->create();
    $column = RaceMarkColumn::factory()->other()->create([
        'race_id' => $nomination->race_id,
        'user_id' => $user->id,
    ]);

    // Act
    $response = $this->actingAs($user)->putJson(nominationColumnMemoUrl($nomination, $column->id), ['content' => 'メモ']);

    // Assert
    $response->assertNotFound();
    $this->assertDatabaseCount('race_nomination_column_memos', 0);
});

test('他ユーザーの印列に印メモを作成しようとすると403が返る', function () {
    // Arrange
    $user = User::factory()->create();
    $nomination = RaceNomination::factory()->create();
    $column = RaceNominationMarkColumn::factory()->create(['race_id' => $nomination->race_id]);

    // Act
    $response = $this->actingAs($user)->putJson(nominationColumnMemoUrl($nomination, $column->id), ['content' => 'メモ']);

    // Assert
    $response->assertForbidden();
});

test('登録馬の印を解除しても印メモは残る', function () {
    // Arrange
    $user = User::factory()->create();
    $nomination = RaceNomination::factory()->create();
    $column = RaceNominationMarkColumn::factory()->create([
        'race_id' => $nomination->race_id,
        'user_id' => $user->id,
    ]);
    RaceNominationColumnMark::factory()->create([
        'race_nomination_mark_column_id' => $column->id,
        'race_nomination_id' => $nomination->id,
    ]);
    $memo = RaceNominationColumnMemo::factory()->create([
        'race_nomination_mark_column_id' => $column->id,
        'race_nomination_id' => $nomination->id,
    ]);

    // Act
    $response = $this->actingAs($user)->putJson(
        '/api/races/'.$nomination->race->uid.'/nomination-mark-columns/'.$column->id.'/nominations/'.$nomination->id.'/mark',
        ['mark_value' => ''],
    );

    // Assert
    $response->assertNoContent();
    $this->assertDatabaseHas('race_nomination_column_memos', ['id' => $memo->id]);
});

// ===== DELETE /api/races/{uid}/nomination-mark-columns/{column_id}/nominations/{nomination_id}/memo =====

test('登録馬の印メモを削除でき204が返る', function () {
    // Arrange
    $user = User::factory()->create();
    $nomination = RaceNomination::factory()->create();
    $column = RaceNominationMarkColumn::factory()->create([
        'race_id' => $nomination->race_id,
        'user_id' => $user->id,
    ]);
    $memo = RaceNominationColumnMemo::factory()->create([
        'race_nomination_mark_column_id' => $column->id,
        'race_nomination_id' => $nomination->id,
    ]);

    // Act
    $response = $this->actingAs($user)->deleteJson(nominationColumnMemoUrl($nomination, $column->id));

    // Assert
    $response->assertNoContent();
    $this->assertDatabaseMissing('race_nomination_column_memos', ['id' => $memo->id]);
});

test('存在しない登録馬の印メモを削除しようとすると404が返る', function () {
    // Arrange
    $user = User::factory()->create();
    $nomination = RaceNomination::factory()->create();
    $column = RaceNominationMarkColumn::factory()->create([
        'race_id' => $nomination->race_id,
        'user_id' => $user->id,
    ]);

    // Act
    $response = $this->actingAs($user)->deleteJson(nominationColumnMemoUrl($nomination, $column->id));

    // Assert
    $response->assertNotFound();
});
