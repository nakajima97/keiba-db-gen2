<?php

use App\Models\Race;
use App\Models\RaceMarkColumn;
use App\Models\User;
use App\Models\Venue;

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

// ===== PATCH /api/races/{uid}/mark-columns/{id} =====

test('未認証ユーザーは印カラムのラベルを更新できない', function () {
    // Arrange
    $user = User::factory()->create();
    $race = createRaceForMarkColumnUpdateTest();
    $column = RaceMarkColumn::factory()
        ->other()
        ->create([
            'race_id' => $race->id,
            'user_id' => $user->id,
            'label' => '友人A',
            'display_order' => 1,
        ]);

    // Act
    $response = $this->patchJson('/api/races/'.$race->uid.'/mark-columns/'.$column->id, [
        'label' => '友人B',
    ]);

    // Assert
    $response->assertUnauthorized();
});

test('認証済みユーザーは他カテゴリのカラムのラベルを更新できる', function () {
    // Arrange
    $user = User::factory()->create();
    $race = createRaceForMarkColumnUpdateTest();
    $column = RaceMarkColumn::factory()
        ->other()
        ->create([
            'race_id' => $race->id,
            'user_id' => $user->id,
            'label' => '友人A',
            'display_order' => 1,
        ]);

    // Act
    $response = $this->actingAs($user)->patchJson('/api/races/'.$race->uid.'/mark-columns/'.$column->id, [
        'label' => '友人B',
    ]);

    // Assert
    $response->assertOk();
    $response->assertJsonPath('data.label', '友人B');
});

test('他ユーザーの印カラムを更新しようとすると403が返る', function () {
    // Arrange
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $race = createRaceForMarkColumnUpdateTest();
    $column = RaceMarkColumn::factory()
        ->other()
        ->create([
            'race_id' => $race->id,
            'user_id' => $otherUser->id,
            'label' => '他人の列',
            'display_order' => 1,
        ]);

    // Act
    $response = $this->actingAs($user)->patchJson('/api/races/'.$race->uid.'/mark-columns/'.$column->id, [
        'label' => '書き換え',
    ]);

    // Assert
    $response->assertForbidden();
});

test('自分カテゴリのカラムを更新しようとすると422が返る', function () {
    // Arrange
    $user = User::factory()->create();
    $race = createRaceForMarkColumnUpdateTest();
    $column = RaceMarkColumn::factory()
        ->own()
        ->create([
            'race_id' => $race->id,
            'user_id' => $user->id,
        ]);

    // Act
    $response = $this->actingAs($user)->patchJson('/api/races/'.$race->uid.'/mark-columns/'.$column->id, [
        'label' => '変更不可',
    ]);

    // Assert
    $response->assertUnprocessable();
});

test('更新時にラベルが32文字を超えると422が返る', function () {
    // Arrange
    $user = User::factory()->create();
    $race = createRaceForMarkColumnUpdateTest();
    $column = RaceMarkColumn::factory()
        ->other()
        ->create([
            'race_id' => $race->id,
            'user_id' => $user->id,
            'label' => '友人A',
            'display_order' => 1,
        ]);

    // Act
    $response = $this->actingAs($user)->patchJson('/api/races/'.$race->uid.'/mark-columns/'.$column->id, [
        'label' => str_repeat('a', 33),
    ]);

    // Assert
    $response->assertUnprocessable();
    $response->assertJsonValidationErrors(['label']);
});

test('存在しない印カラムを更新しようとすると404が返る', function () {
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
