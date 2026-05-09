<?php

use App\Models\Race;
use App\Models\RaceMarkColumn;
use App\Models\User;
use App\Models\Venue;

/**
 * race_mark_columns 追加テスト用の race を作成して返す
 */
function createRaceForMarkColumnStoreTest(): Race
{
    $venue = Venue::firstOrCreate(['name' => '東京']);

    return Race::create([
        'venue_id' => $venue->id,
        'race_date' => '2026-04-26',
        'race_number' => 2,
    ]);
}

// ===== POST /api/races/{uid}/mark-columns =====

test('未認証ユーザーは他カテゴリの印カラムを作成できない', function () {
    // Arrange
    $race = createRaceForMarkColumnStoreTest();

    // Act
    $response = $this->postJson('/api/races/'.$race->uid.'/mark-columns', [
        'label' => '友人A',
    ]);

    // Assert
    $response->assertUnauthorized();
});

test('認証済みユーザーは他カテゴリの印カラムを追加できる', function () {
    // Arrange
    $user = User::factory()->create();
    $race = createRaceForMarkColumnStoreTest();

    // Act
    $response = $this->actingAs($user)->postJson('/api/races/'.$race->uid.'/mark-columns', [
        'label' => '友人A',
    ]);

    // Assert
    $response->assertCreated();
    $response->assertJsonPath('data.type', 'other');
    $response->assertJsonPath('data.label', '友人A');
});

test('他カテゴリの印カラムはラベル空でも作成できる', function () {
    // Arrange
    $user = User::factory()->create();
    $race = createRaceForMarkColumnStoreTest();

    // Act
    $response = $this->actingAs($user)->postJson('/api/races/'.$race->uid.'/mark-columns', [
        'label' => '',
    ]);

    // Assert
    $response->assertCreated();
    $response->assertJsonPath('data.type', 'other');
    $response->assertJsonPath('data.label', '');
});

test('新規の他カテゴリカラム作成時に display_order は末尾に付与される', function () {
    // Arrange
    $user = User::factory()->create();
    $race = createRaceForMarkColumnStoreTest();

    RaceMarkColumn::factory()
        ->own()
        ->create([
            'race_id' => $race->id,
            'user_id' => $user->id,
        ]);
    RaceMarkColumn::factory()
        ->other()
        ->create([
            'race_id' => $race->id,
            'user_id' => $user->id,
            'label' => '友人A',
            'display_order' => 3,
        ]);

    // Act
    $response = $this->actingAs($user)->postJson('/api/races/'.$race->uid.'/mark-columns', [
        'label' => '友人B',
    ]);

    // Assert
    $response->assertCreated();
    $response->assertJsonPath('data.display_order', 4);
});

test('ラベルが32文字を超えると422が返る', function () {
    // Arrange
    $user = User::factory()->create();
    $race = createRaceForMarkColumnStoreTest();

    // Act
    $response = $this->actingAs($user)->postJson('/api/races/'.$race->uid.'/mark-columns', [
        'label' => str_repeat('a', 33),
    ]);

    // Assert
    $response->assertUnprocessable();
    $response->assertJsonValidationErrors(['label']);
});

test('存在しないレースUIDで印カラムを作成しようとすると404が返る', function () {
    // Arrange
    $user = User::factory()->create();

    // Act
    $response = $this->actingAs($user)->postJson('/api/races/non-existent-uid/mark-columns', [
        'label' => '友人A',
    ]);

    // Assert
    $response->assertNotFound();
});
