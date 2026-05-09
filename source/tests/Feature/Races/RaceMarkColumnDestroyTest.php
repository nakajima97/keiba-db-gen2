<?php

use App\Models\Race;
use App\Models\RaceMark;
use App\Models\RaceMarkColumn;
use App\Models\User;
use App\Models\Venue;
use App\Support\NanoId;
use Illuminate\Support\Facades\DB;

/**
 * race_mark_columns 削除テスト用の race を作成して返す
 */
function createRaceForMarkColumnDestroyTest(): Race
{
    $venue = Venue::firstOrCreate(['name' => '東京']);

    return Race::create([
        'venue_id' => $venue->id,
        'race_date' => '2026-04-26',
        'race_number' => 4,
    ]);
}

/**
 * race_entries に 1 件挿入して ID を返す
 */
function insertRaceEntryForDestroy(int $raceId): int
{
    $now = now();
    $horseId = DB::table('horses')->insertGetId([
        'uid' => NanoId::generate(),
        'name' => 'デストロイ用ホース'.uniqid(),
        'birth_year' => 2022,
        'created_at' => $now,
        'updated_at' => $now,
    ]);
    $jockeyId = DB::table('jockeys')->insertGetId([
        'name' => 'デストロイ用騎手'.uniqid(),
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

// ===== DELETE /api/races/{uid}/mark-columns/{id} =====

test('未認証ユーザーは印カラムを削除できない', function () {
    // Arrange
    $user = User::factory()->create();
    $race = createRaceForMarkColumnDestroyTest();
    $column = RaceMarkColumn::factory()
        ->other()
        ->create([
            'race_id' => $race->id,
            'user_id' => $user->id,
            'label' => '友人A',
            'display_order' => 1,
        ]);

    // Act
    $response = $this->deleteJson('/api/races/'.$race->uid.'/mark-columns/'.$column->id);

    // Assert
    $response->assertUnauthorized();
});

test('認証済みユーザーは他カテゴリの印カラムを削除できる', function () {
    // Arrange
    $user = User::factory()->create();
    $race = createRaceForMarkColumnDestroyTest();
    $column = RaceMarkColumn::factory()
        ->other()
        ->create([
            'race_id' => $race->id,
            'user_id' => $user->id,
            'label' => '友人A',
            'display_order' => 1,
        ]);

    // Act
    $response = $this->actingAs($user)->deleteJson('/api/races/'.$race->uid.'/mark-columns/'.$column->id);

    // Assert
    $response->assertNoContent();
    $this->assertDatabaseMissing('race_mark_columns', ['id' => $column->id]);
});

test('他カテゴリの印カラム削除時に関連する race_marks も削除される', function () {
    // Arrange
    $user = User::factory()->create();
    $race = createRaceForMarkColumnDestroyTest();
    $column = RaceMarkColumn::factory()
        ->other()
        ->create([
            'race_id' => $race->id,
            'user_id' => $user->id,
            'label' => '友人A',
            'display_order' => 1,
        ]);
    $raceEntryId = insertRaceEntryForDestroy($race->id);

    RaceMark::factory()->create([
        'race_mark_column_id' => $column->id,
        'race_entry_id' => $raceEntryId,
        'mark_value' => '◎',
    ]);

    // Act
    $this->actingAs($user)->deleteJson('/api/races/'.$race->uid.'/mark-columns/'.$column->id);

    // Assert
    $this->assertDatabaseMissing('race_marks', [
        'race_mark_column_id' => $column->id,
    ]);
});

test('他ユーザーの印カラムを削除しようとすると403が返る', function () {
    // Arrange
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $race = createRaceForMarkColumnDestroyTest();
    $column = RaceMarkColumn::factory()
        ->other()
        ->create([
            'race_id' => $race->id,
            'user_id' => $otherUser->id,
            'label' => '他人の列',
            'display_order' => 1,
        ]);

    // Act
    $response = $this->actingAs($user)->deleteJson('/api/races/'.$race->uid.'/mark-columns/'.$column->id);

    // Assert
    $response->assertForbidden();
});

test('自分カテゴリの印カラムを削除しようとすると422が返る', function () {
    // Arrange
    $user = User::factory()->create();
    $race = createRaceForMarkColumnDestroyTest();
    $column = RaceMarkColumn::factory()
        ->own()
        ->create([
            'race_id' => $race->id,
            'user_id' => $user->id,
        ]);

    // Act
    $response = $this->actingAs($user)->deleteJson('/api/races/'.$race->uid.'/mark-columns/'.$column->id);

    // Assert
    $response->assertUnprocessable();
});

test('存在しない印カラムを削除しようとすると404が返る', function () {
    // Arrange
    $user = User::factory()->create();
    $race = createRaceForMarkColumnDestroyTest();

    // Act
    $response = $this->actingAs($user)->deleteJson('/api/races/'.$race->uid.'/mark-columns/9999999');

    // Assert
    $response->assertNotFound();
});
