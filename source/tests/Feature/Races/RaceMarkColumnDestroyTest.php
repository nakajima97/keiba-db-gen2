<?php

use App\Models\Race;
use App\Models\RaceMark;
use App\Models\RaceMarkColumn;
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

test('authenticated user can delete other mark column', function () {
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

test('related race_marks are also deleted when removing other mark column', function () {
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

test('deleting other users mark column returns 403', function () {
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

test('deleting own column returns 422', function () {
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

test('deleting non-existent mark column returns 404', function () {
    // Arrange
    $user = User::factory()->create();
    $race = createRaceForMarkColumnDestroyTest();

    // Act
    $response = $this->actingAs($user)->deleteJson('/api/races/'.$race->uid.'/mark-columns/9999999');

    // Assert
    $response->assertNotFound();
});
