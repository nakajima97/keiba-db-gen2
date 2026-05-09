<?php

use App\Models\Race;
use App\Models\RaceMark;
use App\Models\RaceMarkColumn;
use App\Models\User;
use App\Models\Venue;
use App\Support\NanoId;
use Illuminate\Support\Facades\DB;

/**
 * race_marks upsert テスト用の race を作成して返す
 */
function createRaceForMarkUpsertTest(): Race
{
    $venue = Venue::firstOrCreate(['name' => '東京']);

    return Race::create([
        'venue_id' => $venue->id,
        'race_date' => '2026-04-26',
        'race_number' => 5,
    ]);
}

/**
 * race_entries に 1 件挿入して ID を返す
 */
function insertRaceEntryForUpsert(int $raceId): int
{
    $now = now();
    $horseId = DB::table('horses')->insertGetId([
        'uid' => NanoId::generate(),
        'name' => 'アップサート用ホース'.uniqid(),
        'birth_year' => 2022,
        'created_at' => $now,
        'updated_at' => $now,
    ]);
    $jockeyId = DB::table('jockeys')->insertGetId([
        'name' => 'アップサート用騎手'.uniqid(),
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
function markUpsertUrl(string $raceUid, int $columnId, int $raceEntryId): string
{
    return '/api/races/'.$raceUid.'/mark-columns/'.$columnId.'/entries/'.$raceEntryId.'/mark';
}

// ===== PUT /api/races/{uid}/mark-columns/{column_id}/entries/{race_entry_id}/mark =====

test('未認証ユーザーはレース印を upsert できない', function () {
    // Arrange
    $user = User::factory()->create();
    $race = createRaceForMarkUpsertTest();
    $column = RaceMarkColumn::factory()
        ->own()
        ->create([
            'race_id' => $race->id,
            'user_id' => $user->id,
        ]);
    $raceEntryId = insertRaceEntryForUpsert($race->id);

    // Act
    $response = $this->putJson(markUpsertUrl($race->uid, $column->id, $raceEntryId), [
        'mark_value' => '◎',
    ]);

    // Assert
    $response->assertUnauthorized();
});

test('認証済みユーザーは新しいレース印を登録できる', function () {
    // Arrange
    $user = User::factory()->create();
    $race = createRaceForMarkUpsertTest();
    $column = RaceMarkColumn::factory()
        ->own()
        ->create([
            'race_id' => $race->id,
            'user_id' => $user->id,
        ]);
    $raceEntryId = insertRaceEntryForUpsert($race->id);

    // Act
    $response = $this->actingAs($user)->putJson(markUpsertUrl($race->uid, $column->id, $raceEntryId), [
        'mark_value' => '◎',
    ]);

    // Assert
    $response->assertOk();
    $response->assertJsonPath('data.mark_value', '◎');
});

test('既存のレース印は別の値に更新できる', function () {
    // Arrange
    $user = User::factory()->create();
    $race = createRaceForMarkUpsertTest();
    $column = RaceMarkColumn::factory()
        ->own()
        ->create([
            'race_id' => $race->id,
            'user_id' => $user->id,
        ]);
    $raceEntryId = insertRaceEntryForUpsert($race->id);

    RaceMark::factory()->create([
        'race_mark_column_id' => $column->id,
        'race_entry_id' => $raceEntryId,
        'mark_value' => '◎',
    ]);

    // Act
    $response = $this->actingAs($user)->putJson(markUpsertUrl($race->uid, $column->id, $raceEntryId), [
        'mark_value' => '○',
    ]);

    // Assert
    $response->assertOk();
    $response->assertJsonPath('data.mark_value', '○');
});

test('mark_value が空のとき既存のレース印が削除される', function () {
    // Arrange
    $user = User::factory()->create();
    $race = createRaceForMarkUpsertTest();
    $column = RaceMarkColumn::factory()
        ->own()
        ->create([
            'race_id' => $race->id,
            'user_id' => $user->id,
        ]);
    $raceEntryId = insertRaceEntryForUpsert($race->id);

    RaceMark::factory()->create([
        'race_mark_column_id' => $column->id,
        'race_entry_id' => $raceEntryId,
        'mark_value' => '◎',
    ]);

    // Act
    $response = $this->actingAs($user)->putJson(markUpsertUrl($race->uid, $column->id, $raceEntryId), [
        'mark_value' => '',
    ]);

    // Assert
    $response->assertNoContent();
    $this->assertDatabaseMissing('race_marks', [
        'race_mark_column_id' => $column->id,
        'race_entry_id' => $raceEntryId,
    ]);
});

test('他ユーザーのカラムに印を upsert しようとすると403が返る', function () {
    // Arrange
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $race = createRaceForMarkUpsertTest();
    $column = RaceMarkColumn::factory()
        ->other()
        ->create([
            'race_id' => $race->id,
            'user_id' => $otherUser->id,
            'label' => '他人の列',
            'display_order' => 1,
        ]);
    $raceEntryId = insertRaceEntryForUpsert($race->id);

    // Act
    $response = $this->actingAs($user)->putJson(markUpsertUrl($race->uid, $column->id, $raceEntryId), [
        'mark_value' => '◎',
    ]);

    // Assert
    $response->assertForbidden();
});

test('mark_value が不正のとき422が返る', function () {
    // Arrange
    $user = User::factory()->create();
    $race = createRaceForMarkUpsertTest();
    $column = RaceMarkColumn::factory()
        ->own()
        ->create([
            'race_id' => $race->id,
            'user_id' => $user->id,
        ]);
    $raceEntryId = insertRaceEntryForUpsert($race->id);

    // Act
    $response = $this->actingAs($user)->putJson(markUpsertUrl($race->uid, $column->id, $raceEntryId), [
        'mark_value' => '★',
    ]);

    // Assert
    $response->assertUnprocessable();
    $response->assertJsonValidationErrors(['mark_value']);
});

test('mark_value=× はもはや受け付けられず422が返る', function () {
    // Arrange
    $user = User::factory()->create();
    $race = createRaceForMarkUpsertTest();
    $column = RaceMarkColumn::factory()
        ->own()
        ->create([
            'race_id' => $race->id,
            'user_id' => $user->id,
        ]);
    $raceEntryId = insertRaceEntryForUpsert($race->id);

    // Act
    $response = $this->actingAs($user)->putJson(markUpsertUrl($race->uid, $column->id, $raceEntryId), [
        'mark_value' => '×',
    ]);

    // Assert
    $response->assertUnprocessable();
    $response->assertJsonValidationErrors(['mark_value']);
});

test('mark_value=☆ を穴馬印として登録できる', function () {
    // Arrange
    $user = User::factory()->create();
    $race = createRaceForMarkUpsertTest();
    $column = RaceMarkColumn::factory()
        ->own()
        ->create([
            'race_id' => $race->id,
            'user_id' => $user->id,
        ]);
    $raceEntryId = insertRaceEntryForUpsert($race->id);

    // Act
    $response = $this->actingAs($user)->putJson(markUpsertUrl($race->uid, $column->id, $raceEntryId), [
        'mark_value' => '☆',
    ]);

    // Assert
    $response->assertOk();
    $response->assertJsonPath('data.mark_value', '☆');
});

test('mark_value が null のとき422が返る', function () {
    // Arrange
    $user = User::factory()->create();
    $race = createRaceForMarkUpsertTest();
    $column = RaceMarkColumn::factory()
        ->own()
        ->create([
            'race_id' => $race->id,
            'user_id' => $user->id,
        ]);
    $raceEntryId = insertRaceEntryForUpsert($race->id);

    // Act
    $response = $this->actingAs($user)->putJson(markUpsertUrl($race->uid, $column->id, $raceEntryId), [
        'mark_value' => null,
    ]);

    // Assert
    $response->assertUnprocessable();
    $response->assertJsonValidationErrors(['mark_value']);
});

test('存在しないカラムに印を upsert しようとすると404が返る', function () {
    // Arrange
    $user = User::factory()->create();
    $race = createRaceForMarkUpsertTest();
    $raceEntryId = insertRaceEntryForUpsert($race->id);

    // Act
    $response = $this->actingAs($user)->putJson(markUpsertUrl($race->uid, 9999999, $raceEntryId), [
        'mark_value' => '◎',
    ]);

    // Assert
    $response->assertNotFound();
});
