<?php

use App\Models\Race;
use App\Models\User;
use App\Models\Venue;
use Illuminate\Support\Facades\DB;

/**
 * race_mark_columns 更新テスト用の race を作成して返す
 */
function createRaceForMarkColumnUpdateTest(): Race
{
    $venue = Venue::firstOrCreate(['name' => '東京']);

    return Race::create([
        'venue_id' => $venue->id,
        'race_date' => '2026-04-26',
        'race_number' => 3,
    ]);
}

/**
 * race_mark_columns に列を 1 件挿入して ID を返す
 *
 * @param  array{race_id:int,user_id:int,column_type:string,label:?string,display_order:int}  $overrides
 */
function insertRaceMarkColumn(array $overrides): int
{
    $now = now();

    return DB::table('race_mark_columns')->insertGetId(array_merge([
        'created_at' => $now,
        'updated_at' => $now,
    ], $overrides));
}

// ===== PATCH /api/races/{uid}/mark-columns/{id} =====

test('unauthenticated user cannot update mark column label', function () {
    // Arrange
    $user = User::factory()->create();
    $race = createRaceForMarkColumnUpdateTest();
    $columnId = insertRaceMarkColumn([
        'race_id' => $race->id,
        'user_id' => $user->id,
        'column_type' => 'other',
        'label' => '友人A',
        'display_order' => 1,
    ]);

    // Act
    $response = $this->patchJson('/api/races/'.$race->uid.'/mark-columns/'.$columnId, [
        'label' => '友人B',
    ]);

    // Assert
    $response->assertUnauthorized();
});

test('authenticated user can update other column label', function () {
    // Arrange
    $user = User::factory()->create();
    $race = createRaceForMarkColumnUpdateTest();
    $columnId = insertRaceMarkColumn([
        'race_id' => $race->id,
        'user_id' => $user->id,
        'column_type' => 'other',
        'label' => '友人A',
        'display_order' => 1,
    ]);

    // Act
    $response = $this->actingAs($user)->patchJson('/api/races/'.$race->uid.'/mark-columns/'.$columnId, [
        'label' => '友人B',
    ]);

    // Assert
    $response->assertOk();
    $response->assertJsonPath('data.label', '友人B');
});

test('updating other users mark column returns 403', function () {
    // Arrange
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $race = createRaceForMarkColumnUpdateTest();
    $columnId = insertRaceMarkColumn([
        'race_id' => $race->id,
        'user_id' => $otherUser->id,
        'column_type' => 'other',
        'label' => '他人の列',
        'display_order' => 1,
    ]);

    // Act
    $response = $this->actingAs($user)->patchJson('/api/races/'.$race->uid.'/mark-columns/'.$columnId, [
        'label' => '書き換え',
    ]);

    // Assert
    $response->assertForbidden();
});

test('updating own column returns 422', function () {
    // Arrange
    $user = User::factory()->create();
    $race = createRaceForMarkColumnUpdateTest();
    $columnId = insertRaceMarkColumn([
        'race_id' => $race->id,
        'user_id' => $user->id,
        'column_type' => 'own',
        'label' => null,
        'display_order' => 0,
    ]);

    // Act
    $response = $this->actingAs($user)->patchJson('/api/races/'.$race->uid.'/mark-columns/'.$columnId, [
        'label' => '変更不可',
    ]);

    // Assert
    $response->assertUnprocessable();
});

test('label exceeding 32 characters returns 422 on update', function () {
    // Arrange
    $user = User::factory()->create();
    $race = createRaceForMarkColumnUpdateTest();
    $columnId = insertRaceMarkColumn([
        'race_id' => $race->id,
        'user_id' => $user->id,
        'column_type' => 'other',
        'label' => '友人A',
        'display_order' => 1,
    ]);

    // Act
    $response = $this->actingAs($user)->patchJson('/api/races/'.$race->uid.'/mark-columns/'.$columnId, [
        'label' => str_repeat('a', 33),
    ]);

    // Assert
    $response->assertUnprocessable();
    $response->assertJsonValidationErrors(['label']);
});

test('updating non-existent mark column returns 404', function () {
    // Arrange
    $user = User::factory()->create();
    $race = createRaceForMarkColumnUpdateTest();

    // Act
    $response = $this->actingAs($user)->patchJson('/api/races/'.$race->uid.'/mark-columns/9999999', [
        'label' => '存在しない',
    ]);

    // Assert
    $response->assertNotFound();
});
