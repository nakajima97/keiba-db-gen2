<?php

use App\Models\Race;
use App\Models\RaceMarkColumn;
use App\Models\User;
use App\Models\Venue;
use App\Support\NanoId;
use Illuminate\Support\Facades\DB;
use Spectator\Spectator;

beforeEach(function () {
    Spectator::using('openapi.yaml');
});

/**
 * 契約テスト用の race を 1 件作成する
 */
function createRaceForContractTest(int $raceNumber): Race
{
    $venue = Venue::firstOrCreate(['name' => '東京']);

    return Race::create([
        'venue_id' => $venue->id,
        'race_date' => '2026-04-26',
        'race_number' => $raceNumber,
    ]);
}

/**
 * race_entries に 1 件挿入して ID を返す
 */
function insertRaceEntryForContractTest(int $raceId): int
{
    $now = now();
    $horseId = DB::table('horses')->insertGetId([
        'uid' => NanoId::generate(),
        'name' => '契約テスト用ホース'.uniqid(),
        'birth_year' => 2022,
        'created_at' => $now,
        'updated_at' => $now,
    ]);
    $jockeyId = DB::table('jockeys')->insertGetId([
        'name' => '契約テスト用騎手'.uniqid(),
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

// ===== GET /api/races/{uid}/mark-columns =====

test('契約: GET listRaceMarkColumns が OpenAPI 仕様と一致する', function () {
    // Arrange
    $user = User::factory()->create();
    $race = createRaceForContractTest(1);

    // Act
    $response = $this->actingAs($user)
        ->getJson('/api/races/'.$race->uid.'/mark-columns');

    // Assert
    $response->assertValidRequest()->assertValidResponse(200);
});

// ===== POST /api/races/{uid}/mark-columns =====

test('契約: POST createOtherRaceMarkColumn が OpenAPI 仕様と一致する', function () {
    // Arrange
    $user = User::factory()->create();
    $race = createRaceForContractTest(2);

    // Act
    $response = $this->actingAs($user)
        ->postJson('/api/races/'.$race->uid.'/mark-columns', [
            'label' => '友人A',
        ]);

    // Assert
    $response->assertValidRequest()->assertValidResponse(201);
});

// ===== PATCH /api/races/{uid}/mark-columns/{id} =====

test('契約: PATCH updateRaceMarkColumnLabel が OpenAPI 仕様と一致する', function () {
    // Arrange
    $user = User::factory()->create();
    $race = createRaceForContractTest(3);
    $column = RaceMarkColumn::factory()
        ->other()
        ->create([
            'race_id' => $race->id,
            'user_id' => $user->id,
            'label' => '旧ラベル',
            'display_order' => 1,
        ]);

    // Act
    $response = $this->actingAs($user)
        ->patchJson('/api/races/'.$race->uid.'/mark-columns/'.$column->id, [
            'label' => '新ラベル',
        ]);

    // Assert
    $response->assertValidRequest()->assertValidResponse(200);
});

// ===== DELETE /api/races/{uid}/mark-columns/{id} =====

test('契約: DELETE deleteRaceMarkColumn が OpenAPI 仕様と一致する', function () {
    // Arrange
    $user = User::factory()->create();
    $race = createRaceForContractTest(4);
    $column = RaceMarkColumn::factory()
        ->other()
        ->create([
            'race_id' => $race->id,
            'user_id' => $user->id,
            'label' => '削除対象',
            'display_order' => 1,
        ]);

    // Act
    $response = $this->actingAs($user)
        ->deleteJson('/api/races/'.$race->uid.'/mark-columns/'.$column->id);

    // Assert
    $response->assertValidRequest()->assertValidResponse(204);
});

// ===== PUT /api/races/{uid}/mark-columns/{column_id}/entries/{race_entry_id}/mark =====

test('契約: PUT putRaceMark が OpenAPI 仕様と一致する', function () {
    // Arrange
    $user = User::factory()->create();
    $race = createRaceForContractTest(5);
    $column = RaceMarkColumn::factory()
        ->own()
        ->create([
            'race_id' => $race->id,
            'user_id' => $user->id,
        ]);
    $raceEntryId = insertRaceEntryForContractTest($race->id);

    // Act
    $response = $this->actingAs($user)
        ->putJson('/api/races/'.$race->uid.'/mark-columns/'.$column->id.'/entries/'.$raceEntryId.'/mark', [
            'mark_value' => '◎',
        ]);

    // Assert
    $response->assertValidRequest()->assertValidResponse(200);
});

// ===== PUT /api/races/{uid}/mark-columns/{column_id}/entries/{race_entry_id}/memo =====

test('契約: PUT putRaceMarkMemo が OpenAPI 仕様と一致する', function () {
    // Arrange
    $user = User::factory()->create();
    $race = createRaceForContractTest(6);
    $column = RaceMarkColumn::factory()
        ->other()
        ->create([
            'race_id' => $race->id,
            'user_id' => $user->id,
            'label' => '他人の列',
            'display_order' => 1,
        ]);
    $raceEntryId = insertRaceEntryForContractTest($race->id);

    // Act
    $response = $this->actingAs($user)
        ->putJson('/api/races/'.$race->uid.'/mark-columns/'.$column->id.'/entries/'.$raceEntryId.'/memo', [
            'content' => '契約テストメモ',
        ]);

    // Assert
    $response->assertValidRequest()->assertValidResponse(201);
});

// ===== DELETE /api/races/{uid}/mark-columns/{column_id}/entries/{race_entry_id}/memo =====

test('契約: DELETE deleteRaceMarkMemo が OpenAPI 仕様と一致する', function () {
    // Arrange
    $user = User::factory()->create();
    $race = createRaceForContractTest(7);
    $column = RaceMarkColumn::factory()
        ->other()
        ->create([
            'race_id' => $race->id,
            'user_id' => $user->id,
            'label' => '他人の列',
            'display_order' => 1,
        ]);
    $raceEntryId = insertRaceEntryForContractTest($race->id);
    DB::table('race_mark_memos')->insert([
        'race_mark_column_id' => $column->id,
        'race_entry_id' => $raceEntryId,
        'content' => '削除対象メモ',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    // Act
    $response = $this->actingAs($user)
        ->deleteJson('/api/races/'.$race->uid.'/mark-columns/'.$column->id.'/entries/'.$raceEntryId.'/memo');

    // Assert
    $response->assertValidRequest()->assertValidResponse(204);
});
