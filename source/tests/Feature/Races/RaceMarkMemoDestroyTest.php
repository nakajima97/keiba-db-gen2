<?php

use App\Models\Race;
use App\Models\RaceMarkColumn;
use App\Models\User;
use App\Models\Venue;
use Illuminate\Support\Facades\DB;

/**
 * race_mark_memos destroy テスト用の race を作成して返す
 */
function createRaceForMarkMemoDestroyTest(): Race
{
    $venue = Venue::firstOrCreate(['name' => '東京']);

    return Race::create([
        'venue_id' => $venue->id,
        'race_date' => '2026-04-26',
        'race_number' => 7,
    ]);
}

/**
 * race_entries に 1 件挿入して ID を返す
 */
function insertRaceEntryForMarkMemoDestroy(int $raceId): int
{
    $now = now();
    $horseId = DB::table('horses')->insertGetId([
        'name' => 'メモ削除用ホース'.uniqid(),
        'birth_year' => 2022,
        'created_at' => $now,
        'updated_at' => $now,
    ]);
    $jockeyId = DB::table('jockeys')->insertGetId([
        'name' => 'メモ削除用騎手'.uniqid(),
        'created_at' => $now,
        'updated_at' => $now,
    ]);

    return DB::table('race_entries')->insertGetId([
        'race_id' => $raceId,
        'horse_id' => $horseId,
        'jockey_id' => $jockeyId,
        'frame_number' => 1,
        'horse_number' => 1,
        'weight' => 55.0,
        'horse_weight' => 480,
        'created_at' => $now,
        'updated_at' => $now,
    ]);
}

/**
 * URL ヘルパ
 */
function markMemoDestroyUrl(string $raceUid, int $columnId, int $raceEntryId): string
{
    return '/api/races/'.$raceUid.'/mark-columns/'.$columnId.'/entries/'.$raceEntryId.'/memo';
}

/**
 * race_mark_memos に 1 件挿入して ID を返す
 */
function insertRaceMarkMemoForDestroy(int $columnId, int $raceEntryId, string $content = '既存メモ'): int
{
    $now = now();

    return DB::table('race_mark_memos')->insertGetId([
        'race_mark_column_id' => $columnId,
        'race_entry_id' => $raceEntryId,
        'content' => $content,
        'created_at' => $now,
        'updated_at' => $now,
    ]);
}

// ===== DELETE /api/races/{uid}/mark-columns/{column_id}/entries/{race_entry_id}/memo =====

test('unauthenticated user cannot delete race mark memo', function () {
    // Arrange
    $user = User::factory()->create();
    $race = createRaceForMarkMemoDestroyTest();
    $column = RaceMarkColumn::factory()
        ->other()
        ->create([
            'race_id' => $race->id,
            'user_id' => $user->id,
            'label' => '他人の列',
            'display_order' => 1,
        ]);
    $raceEntryId = insertRaceEntryForMarkMemoDestroy($race->id);
    insertRaceMarkMemoForDestroy($column->id, $raceEntryId);

    // Act
    $response = $this->deleteJson(markMemoDestroyUrl($race->uid, $column->id, $raceEntryId));

    // Assert
    $response->assertUnauthorized();
});

test('authenticated user can delete race mark memo', function () {
    // Arrange
    $user = User::factory()->create();
    $race = createRaceForMarkMemoDestroyTest();
    $column = RaceMarkColumn::factory()
        ->other()
        ->create([
            'race_id' => $race->id,
            'user_id' => $user->id,
            'label' => '他人の列',
            'display_order' => 1,
        ]);
    $raceEntryId = insertRaceEntryForMarkMemoDestroy($race->id);
    insertRaceMarkMemoForDestroy($column->id, $raceEntryId);

    // Act
    $response = $this->actingAs($user)->deleteJson(markMemoDestroyUrl($race->uid, $column->id, $raceEntryId));

    // Assert
    $response->assertNoContent();
    $this->assertDatabaseMissing('race_mark_memos', [
        'race_mark_column_id' => $column->id,
        'race_entry_id' => $raceEntryId,
    ]);
});

test('deleting memo on own column returns 422', function () {
    // Arrange
    $user = User::factory()->create();
    $race = createRaceForMarkMemoDestroyTest();
    $column = RaceMarkColumn::factory()
        ->own()
        ->create([
            'race_id' => $race->id,
            'user_id' => $user->id,
        ]);
    $raceEntryId = insertRaceEntryForMarkMemoDestroy($race->id);

    // Act
    $response = $this->actingAs($user)->deleteJson(markMemoDestroyUrl($race->uid, $column->id, $raceEntryId));

    // Assert
    $response->assertUnprocessable();
});

test('deleting memo on other users column returns 403', function () {
    // Arrange
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $race = createRaceForMarkMemoDestroyTest();
    $column = RaceMarkColumn::factory()
        ->other()
        ->create([
            'race_id' => $race->id,
            'user_id' => $otherUser->id,
            'label' => '他人の列',
            'display_order' => 1,
        ]);
    $raceEntryId = insertRaceEntryForMarkMemoDestroy($race->id);
    insertRaceMarkMemoForDestroy($column->id, $raceEntryId);

    // Act
    $response = $this->actingAs($user)->deleteJson(markMemoDestroyUrl($race->uid, $column->id, $raceEntryId));

    // Assert
    $response->assertForbidden();
});

test('deleting non-existent memo returns 404', function () {
    // Arrange
    $user = User::factory()->create();
    $race = createRaceForMarkMemoDestroyTest();
    $column = RaceMarkColumn::factory()
        ->other()
        ->create([
            'race_id' => $race->id,
            'user_id' => $user->id,
            'label' => '他人の列',
            'display_order' => 1,
        ]);
    $raceEntryId = insertRaceEntryForMarkMemoDestroy($race->id);

    // Act
    $response = $this->actingAs($user)->deleteJson(markMemoDestroyUrl($race->uid, $column->id, $raceEntryId));

    // Assert
    $response->assertNotFound();
});

test('deleting memo on non-existent column id returns 404', function () {
    // Arrange
    $user = User::factory()->create();
    $race = createRaceForMarkMemoDestroyTest();
    $raceEntryId = insertRaceEntryForMarkMemoDestroy($race->id);

    // Act
    $response = $this->actingAs($user)->deleteJson(markMemoDestroyUrl($race->uid, 9999999, $raceEntryId));

    // Assert
    $response->assertNotFound();
});
