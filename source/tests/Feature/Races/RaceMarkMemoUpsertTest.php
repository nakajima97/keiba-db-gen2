<?php

use App\Models\Race;
use App\Models\RaceMarkColumn;
use App\Models\User;
use App\Models\Venue;
use App\Support\NanoId;
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
        'uid' => NanoId::generate(),
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
        'uid' => NanoId::generate(),
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

test('未認証ユーザーはレース印メモを upsert できない', function () {
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

test('認証済みユーザーは他カテゴリのカラムに新規メモを作成できる', function () {
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

test('既存のレース印メモは upsert で更新できる', function () {
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

test('race_marks レコードが存在しなくてもメモを作成できる', function () {
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

test('自分カテゴリのカラムにメモを作成しようとすると422が返る', function () {
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

test('他ユーザーのカラムにメモを作成しようとすると403が返る', function () {
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

test('本文が空のとき422が返る', function () {
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

test('本文が1000文字を超えると422が返る', function () {
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

test('本文が未指定のとき422が返る', function () {
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

test('ちょうど1000文字の本文は受け付けられる', function () {
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

test('存在しないレースUIDでメモを upsert しようとすると404が返る', function () {
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

test('存在しないカラムIDでメモを upsert しようとすると404が返る', function () {
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

test('存在しない出馬IDでメモを upsert しようとすると404が返る', function () {
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
