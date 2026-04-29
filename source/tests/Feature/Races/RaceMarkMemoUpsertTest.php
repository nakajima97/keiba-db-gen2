<?php

use App\Models\Race;
use App\Models\RaceMarkColumn;
use App\Models\User;
use App\Models\Venue;
use Illuminate\Support\Facades\DB;

/**
 * race_mark_memos upsert テスト用の race を作成して返す
 */
function createRaceForMarkMemoUpsertTest(): Race
{
    $venue = Venue::firstOrCreate(['name' => '東京']);

    return Race::create([
        'venue_id' => $venue->id,
        'race_date' => '2026-04-26',
        'race_number' => 6,
    ]);
}

/**
 * race_entries に 1 件挿入して ID を返す
 */
function insertRaceEntryForMarkMemoUpsert(int $raceId): int
{
    $now = now();
    $horseId = DB::table('horses')->insertGetId([
        'name' => 'メモアップサート用ホース'.uniqid(),
        'birth_year' => 2022,
        'created_at' => $now,
        'updated_at' => $now,
    ]);
    $jockeyId = DB::table('jockeys')->insertGetId([
        'name' => 'メモアップサート用騎手'.uniqid(),
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
function markMemoUrl(string $raceUid, int $columnId, int $raceEntryId): string
{
    return '/api/races/'.$raceUid.'/mark-columns/'.$columnId.'/entries/'.$raceEntryId.'/memo';
}

// ===== PUT /api/races/{uid}/mark-columns/{column_id}/entries/{race_entry_id}/memo =====

test('unauthenticated user cannot upsert race mark memo', function () {
    // Arrange
    $user = User::factory()->create();
    $race = createRaceForMarkMemoUpsertTest();
    $column = RaceMarkColumn::factory()
        ->other()
        ->create([
            'race_id' => $race->id,
            'user_id' => $user->id,
            'label' => '他人の列',
            'display_order' => 1,
        ]);
    $raceEntryId = insertRaceEntryForMarkMemoUpsert($race->id);

    // Act
    $response = $this->putJson(markMemoUrl($race->uid, $column->id, $raceEntryId), [
        'content' => 'テストメモ',
    ]);

    // Assert
    $response->assertUnauthorized();
});

test('authenticated user can create new race mark memo on other column', function () {
    // Arrange
    $user = User::factory()->create();
    $race = createRaceForMarkMemoUpsertTest();
    $column = RaceMarkColumn::factory()
        ->other()
        ->create([
            'race_id' => $race->id,
            'user_id' => $user->id,
            'label' => '他人の列',
            'display_order' => 1,
        ]);
    $raceEntryId = insertRaceEntryForMarkMemoUpsert($race->id);

    // Act
    $response = $this->actingAs($user)->putJson(markMemoUrl($race->uid, $column->id, $raceEntryId), [
        'content' => 'テストメモ',
    ]);

    // Assert
    $response->assertCreated();
    $response->assertJsonPath('data.content', 'テストメモ');
    $this->assertDatabaseHas('race_mark_memos', [
        'race_mark_column_id' => $column->id,
        'race_entry_id' => $raceEntryId,
        'content' => 'テストメモ',
    ]);
});

test('existing race mark memo can be updated via upsert', function () {
    // Arrange
    $user = User::factory()->create();
    $race = createRaceForMarkMemoUpsertTest();
    $column = RaceMarkColumn::factory()
        ->other()
        ->create([
            'race_id' => $race->id,
            'user_id' => $user->id,
            'label' => '他人の列',
            'display_order' => 1,
        ]);
    $raceEntryId = insertRaceEntryForMarkMemoUpsert($race->id);

    $now = now();
    DB::table('race_mark_memos')->insert([
        'race_mark_column_id' => $column->id,
        'race_entry_id' => $raceEntryId,
        'content' => '更新前',
        'created_at' => $now,
        'updated_at' => $now,
    ]);

    // Act
    $response = $this->actingAs($user)->putJson(markMemoUrl($race->uid, $column->id, $raceEntryId), [
        'content' => '更新後',
    ]);

    // Assert
    $response->assertOk();
    $response->assertJsonPath('data.content', '更新後');
    $this->assertDatabaseHas('race_mark_memos', [
        'race_mark_column_id' => $column->id,
        'race_entry_id' => $raceEntryId,
        'content' => '更新後',
    ]);
    expect(DB::table('race_mark_memos')->where([
        'race_mark_column_id' => $column->id,
        'race_entry_id' => $raceEntryId,
    ])->count())->toBe(1);
});

test('memo can be created even when race_marks record does not exist', function () {
    // Arrange
    $user = User::factory()->create();
    $race = createRaceForMarkMemoUpsertTest();
    $column = RaceMarkColumn::factory()
        ->other()
        ->create([
            'race_id' => $race->id,
            'user_id' => $user->id,
            'label' => '他人の列',
            'display_order' => 1,
        ]);
    $raceEntryId = insertRaceEntryForMarkMemoUpsert($race->id);

    // Act
    $response = $this->actingAs($user)->putJson(markMemoUrl($race->uid, $column->id, $raceEntryId), [
        'content' => '印なしでもメモ作成',
    ]);

    // Assert
    $response->assertCreated();
    $this->assertDatabaseHas('race_mark_memos', [
        'race_mark_column_id' => $column->id,
        'race_entry_id' => $raceEntryId,
        'content' => '印なしでもメモ作成',
    ]);
});

test('creating memo on own column returns 422', function () {
    // Arrange
    $user = User::factory()->create();
    $race = createRaceForMarkMemoUpsertTest();
    $column = RaceMarkColumn::factory()
        ->own()
        ->create([
            'race_id' => $race->id,
            'user_id' => $user->id,
        ]);
    $raceEntryId = insertRaceEntryForMarkMemoUpsert($race->id);

    // Act
    $response = $this->actingAs($user)->putJson(markMemoUrl($race->uid, $column->id, $raceEntryId), [
        'content' => '自分の列にメモ',
    ]);

    // Assert
    $response->assertUnprocessable();
});

test('creating memo on other users column returns 403', function () {
    // Arrange
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $race = createRaceForMarkMemoUpsertTest();
    $column = RaceMarkColumn::factory()
        ->other()
        ->create([
            'race_id' => $race->id,
            'user_id' => $otherUser->id,
            'label' => '他人の列',
            'display_order' => 1,
        ]);
    $raceEntryId = insertRaceEntryForMarkMemoUpsert($race->id);

    // Act
    $response = $this->actingAs($user)->putJson(markMemoUrl($race->uid, $column->id, $raceEntryId), [
        'content' => '他人の列にメモ',
    ]);

    // Assert
    $response->assertForbidden();
});

test('empty content returns 422', function () {
    // Arrange
    $user = User::factory()->create();
    $race = createRaceForMarkMemoUpsertTest();
    $column = RaceMarkColumn::factory()
        ->other()
        ->create([
            'race_id' => $race->id,
            'user_id' => $user->id,
            'label' => '他人の列',
            'display_order' => 1,
        ]);
    $raceEntryId = insertRaceEntryForMarkMemoUpsert($race->id);

    // Act
    $response = $this->actingAs($user)->putJson(markMemoUrl($race->uid, $column->id, $raceEntryId), [
        'content' => '',
    ]);

    // Assert
    $response->assertUnprocessable();
    $response->assertJsonValidationErrors(['content']);
});

test('content exceeding 1000 characters returns 422', function () {
    // Arrange
    $user = User::factory()->create();
    $race = createRaceForMarkMemoUpsertTest();
    $column = RaceMarkColumn::factory()
        ->other()
        ->create([
            'race_id' => $race->id,
            'user_id' => $user->id,
            'label' => '他人の列',
            'display_order' => 1,
        ]);
    $raceEntryId = insertRaceEntryForMarkMemoUpsert($race->id);

    // Act
    $response = $this->actingAs($user)->putJson(markMemoUrl($race->uid, $column->id, $raceEntryId), [
        'content' => str_repeat('あ', 1001),
    ]);

    // Assert
    $response->assertUnprocessable();
    $response->assertJsonValidationErrors(['content']);
});

test('missing content returns 422', function () {
    // Arrange
    $user = User::factory()->create();
    $race = createRaceForMarkMemoUpsertTest();
    $column = RaceMarkColumn::factory()
        ->other()
        ->create([
            'race_id' => $race->id,
            'user_id' => $user->id,
            'label' => '他人の列',
            'display_order' => 1,
        ]);
    $raceEntryId = insertRaceEntryForMarkMemoUpsert($race->id);

    // Act
    $response = $this->actingAs($user)->putJson(markMemoUrl($race->uid, $column->id, $raceEntryId), []);

    // Assert
    $response->assertUnprocessable();
    $response->assertJsonValidationErrors(['content']);
});

test('content with exactly 1000 characters is accepted', function () {
    // Arrange
    $user = User::factory()->create();
    $race = createRaceForMarkMemoUpsertTest();
    $column = RaceMarkColumn::factory()
        ->other()
        ->create([
            'race_id' => $race->id,
            'user_id' => $user->id,
            'label' => '他人の列',
            'display_order' => 1,
        ]);
    $raceEntryId = insertRaceEntryForMarkMemoUpsert($race->id);

    // Act
    $response = $this->actingAs($user)->putJson(markMemoUrl($race->uid, $column->id, $raceEntryId), [
        'content' => str_repeat('あ', 1000),
    ]);

    // Assert
    $response->assertCreated();
    $response->assertJsonPath('data.content', str_repeat('あ', 1000));
});

test('upserting memo on non-existent race uid returns 404', function () {
    // Arrange
    $user = User::factory()->create();
    $race = createRaceForMarkMemoUpsertTest();
    $column = RaceMarkColumn::factory()
        ->other()
        ->create([
            'race_id' => $race->id,
            'user_id' => $user->id,
            'label' => '他人の列',
            'display_order' => 1,
        ]);
    $raceEntryId = insertRaceEntryForMarkMemoUpsert($race->id);

    // Act
    $response = $this->actingAs($user)->putJson(markMemoUrl('non-existent-uid', $column->id, $raceEntryId), [
        'content' => 'テストメモ',
    ]);

    // Assert
    $response->assertNotFound();
});

test('upserting memo on non-existent column id returns 404', function () {
    // Arrange
    $user = User::factory()->create();
    $race = createRaceForMarkMemoUpsertTest();
    $raceEntryId = insertRaceEntryForMarkMemoUpsert($race->id);

    // Act
    $response = $this->actingAs($user)->putJson(markMemoUrl($race->uid, 9999999, $raceEntryId), [
        'content' => 'テストメモ',
    ]);

    // Assert
    $response->assertNotFound();
});

test('upserting memo on non-existent race entry id returns 404', function () {
    // Arrange
    $user = User::factory()->create();
    $race = createRaceForMarkMemoUpsertTest();
    $column = RaceMarkColumn::factory()
        ->other()
        ->create([
            'race_id' => $race->id,
            'user_id' => $user->id,
            'label' => '他人の列',
            'display_order' => 1,
        ]);

    // Act
    $response = $this->actingAs($user)->putJson(markMemoUrl($race->uid, $column->id, 9999999), [
        'content' => 'テストメモ',
    ]);

    // Assert
    $response->assertNotFound();
});
