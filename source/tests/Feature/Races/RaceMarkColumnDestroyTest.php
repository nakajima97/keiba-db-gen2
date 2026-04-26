<?php

use App\Models\Race;
use App\Models\User;
use App\Models\Venue;
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
 * race_mark_columns に列を 1 件挿入して ID を返す
 *
 * @param  array{race_id:int,user_id:int,column_type:string,label:?string,display_order:int}  $overrides
 */
function insertRaceMarkColumnForDestroy(array $overrides): int
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
function insertRaceEntryForDestroy(int $raceId): int
{
    $now = now();
    $horseId = DB::table('horses')->insertGetId([
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

test('unauthenticated user cannot delete mark column', function () {
    // Arrange
    $user = User::factory()->create();
    $race = createRaceForMarkColumnDestroyTest();
    $columnId = insertRaceMarkColumnForDestroy([
        'race_id' => $race->id,
        'user_id' => $user->id,
        'column_type' => 'other',
        'label' => '友人A',
        'display_order' => 1,
    ]);

    // Act
    $response = $this->deleteJson('/api/races/'.$race->uid.'/mark-columns/'.$columnId);

    // Assert
    $response->assertUnauthorized();
});

test('authenticated user can delete other mark column', function () {
    // Arrange
    $user = User::factory()->create();
    $race = createRaceForMarkColumnDestroyTest();
    $columnId = insertRaceMarkColumnForDestroy([
        'race_id' => $race->id,
        'user_id' => $user->id,
        'column_type' => 'other',
        'label' => '友人A',
        'display_order' => 1,
    ]);

    // Act
    $response = $this->actingAs($user)->deleteJson('/api/races/'.$race->uid.'/mark-columns/'.$columnId);

    // Assert
    $response->assertNoContent();
    $this->assertDatabaseMissing('race_mark_columns', ['id' => $columnId]);
});

test('related race_marks are also deleted when removing other mark column', function () {
    // Arrange
    $user = User::factory()->create();
    $race = createRaceForMarkColumnDestroyTest();
    $columnId = insertRaceMarkColumnForDestroy([
        'race_id' => $race->id,
        'user_id' => $user->id,
        'column_type' => 'other',
        'label' => '友人A',
        'display_order' => 1,
    ]);
    $raceEntryId = insertRaceEntryForDestroy($race->id);
    $now = now();

    DB::table('race_marks')->insert([
        'race_mark_column_id' => $columnId,
        'race_entry_id' => $raceEntryId,
        'mark_value' => '◎',
        'created_at' => $now,
        'updated_at' => $now,
    ]);

    // Act
    $this->actingAs($user)->deleteJson('/api/races/'.$race->uid.'/mark-columns/'.$columnId);

    // Assert
    $this->assertDatabaseMissing('race_marks', [
        'race_mark_column_id' => $columnId,
    ]);
});

test('deleting other users mark column returns 403', function () {
    // Arrange
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $race = createRaceForMarkColumnDestroyTest();
    $columnId = insertRaceMarkColumnForDestroy([
        'race_id' => $race->id,
        'user_id' => $otherUser->id,
        'column_type' => 'other',
        'label' => '他人の列',
        'display_order' => 1,
    ]);

    // Act
    $response = $this->actingAs($user)->deleteJson('/api/races/'.$race->uid.'/mark-columns/'.$columnId);

    // Assert
    $response->assertForbidden();
});

test('deleting own column returns 422', function () {
    // Arrange
    $user = User::factory()->create();
    $race = createRaceForMarkColumnDestroyTest();
    $columnId = insertRaceMarkColumnForDestroy([
        'race_id' => $race->id,
        'user_id' => $user->id,
        'column_type' => 'own',
        'label' => null,
        'display_order' => 0,
    ]);

    // Act
    $response = $this->actingAs($user)->deleteJson('/api/races/'.$race->uid.'/mark-columns/'.$columnId);

    // Assert
    $response->assertUnprocessable();
});

test('deleting non-existent mark column returns 404', function () {
    // Arrange
    $user = User::factory()->create();
    $race = createRaceForMarkColumnDestroyTest();

    // Act
    $response = $this->actingAs($user)->deleteJson('/api/races/'.$race->uid.'/mark-columns/9999999');

    // Assert
    $response->assertNotFound();
});
