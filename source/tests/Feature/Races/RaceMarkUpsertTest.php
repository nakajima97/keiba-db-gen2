<?php

use App\Models\Race;
use App\Models\User;
use App\Models\Venue;
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
 * race_mark_columns に列を 1 件挿入して ID を返す
 *
 * @param  array{race_id:int,user_id:int,column_type:string,label:?string,display_order:int}  $overrides
 */
function insertRaceMarkColumnForUpsert(array $overrides): int
{
    $now = now();

    return DB::table('race_mark_columns')->insertGetId(array_merge([
        'created_at' => $now,
        'updated_at' => $now,
    ], $overrides));
}

/**
 * race_entries に 1 件挿入して ID を返す
 */
function insertRaceEntryForUpsert(int $raceId): int
{
    $now = now();
    $horseId = DB::table('horses')->insertGetId([
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

test('unauthenticated user cannot upsert race mark', function () {
    // Arrange
    $user = User::factory()->create();
    $race = createRaceForMarkUpsertTest();
    $columnId = insertRaceMarkColumnForUpsert([
        'race_id' => $race->id,
        'user_id' => $user->id,
        'column_type' => 'own',
        'label' => null,
        'display_order' => 0,
    ]);
    $raceEntryId = insertRaceEntryForUpsert($race->id);

    // Act
    $response = $this->putJson(markUpsertUrl($race->uid, $columnId, $raceEntryId), [
        'mark_value' => '◎',
    ]);

    // Assert
    $response->assertUnauthorized();
});

test('authenticated user can set new race mark', function () {
    // Arrange
    $user = User::factory()->create();
    $race = createRaceForMarkUpsertTest();
    $columnId = insertRaceMarkColumnForUpsert([
        'race_id' => $race->id,
        'user_id' => $user->id,
        'column_type' => 'own',
        'label' => null,
        'display_order' => 0,
    ]);
    $raceEntryId = insertRaceEntryForUpsert($race->id);

    // Act
    $response = $this->actingAs($user)->putJson(markUpsertUrl($race->uid, $columnId, $raceEntryId), [
        'mark_value' => '◎',
    ]);

    // Assert
    $response->assertOk();
    $response->assertJsonPath('data.mark_value', '◎');
});

test('existing race mark can be updated to a different value', function () {
    // Arrange
    $user = User::factory()->create();
    $race = createRaceForMarkUpsertTest();
    $columnId = insertRaceMarkColumnForUpsert([
        'race_id' => $race->id,
        'user_id' => $user->id,
        'column_type' => 'own',
        'label' => null,
        'display_order' => 0,
    ]);
    $raceEntryId = insertRaceEntryForUpsert($race->id);
    $now = now();

    DB::table('race_marks')->insert([
        'race_mark_column_id' => $columnId,
        'race_entry_id' => $raceEntryId,
        'mark_value' => '◎',
        'created_at' => $now,
        'updated_at' => $now,
    ]);

    // Act
    $response = $this->actingAs($user)->putJson(markUpsertUrl($race->uid, $columnId, $raceEntryId), [
        'mark_value' => '○',
    ]);

    // Assert
    $response->assertOk();
    $response->assertJsonPath('data.mark_value', '○');
});

test('empty mark_value removes the existing race mark', function () {
    // Arrange
    $user = User::factory()->create();
    $race = createRaceForMarkUpsertTest();
    $columnId = insertRaceMarkColumnForUpsert([
        'race_id' => $race->id,
        'user_id' => $user->id,
        'column_type' => 'own',
        'label' => null,
        'display_order' => 0,
    ]);
    $raceEntryId = insertRaceEntryForUpsert($race->id);
    $now = now();

    DB::table('race_marks')->insert([
        'race_mark_column_id' => $columnId,
        'race_entry_id' => $raceEntryId,
        'mark_value' => '◎',
        'created_at' => $now,
        'updated_at' => $now,
    ]);

    // Act
    $response = $this->actingAs($user)->putJson(markUpsertUrl($race->uid, $columnId, $raceEntryId), [
        'mark_value' => '',
    ]);

    // Assert
    $response->assertNoContent();
    $this->assertDatabaseMissing('race_marks', [
        'race_mark_column_id' => $columnId,
        'race_entry_id' => $raceEntryId,
    ]);
});

test('upserting mark on other users column returns 403', function () {
    // Arrange
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $race = createRaceForMarkUpsertTest();
    $columnId = insertRaceMarkColumnForUpsert([
        'race_id' => $race->id,
        'user_id' => $otherUser->id,
        'column_type' => 'other',
        'label' => '他人の列',
        'display_order' => 1,
    ]);
    $raceEntryId = insertRaceEntryForUpsert($race->id);

    // Act
    $response = $this->actingAs($user)->putJson(markUpsertUrl($race->uid, $columnId, $raceEntryId), [
        'mark_value' => '◎',
    ]);

    // Assert
    $response->assertForbidden();
});

test('invalid mark_value returns 422', function () {
    // Arrange
    $user = User::factory()->create();
    $race = createRaceForMarkUpsertTest();
    $columnId = insertRaceMarkColumnForUpsert([
        'race_id' => $race->id,
        'user_id' => $user->id,
        'column_type' => 'own',
        'label' => null,
        'display_order' => 0,
    ]);
    $raceEntryId = insertRaceEntryForUpsert($race->id);

    // Act
    $response = $this->actingAs($user)->putJson(markUpsertUrl($race->uid, $columnId, $raceEntryId), [
        'mark_value' => '★',
    ]);

    // Assert
    $response->assertUnprocessable();
    $response->assertJsonValidationErrors(['mark_value']);
});

test('upserting mark on non-existent column returns 404', function () {
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
