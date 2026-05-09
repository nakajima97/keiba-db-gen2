<?php

use App\Models\Race;
use App\Models\RaceMarkColumn;
use App\Models\User;
use App\Models\Venue;

/**
 * race_mark_columns テスト用の race を作成して返す
 */
function createRaceForMarkColumnIndexTest(): Race
{
    $venue = Venue::firstOrCreate(['name' => '東京']);

    return Race::create([
        'venue_id' => $venue->id,
        'race_date' => '2026-04-26',
        'race_number' => 1,
    ]);
}

// ===== GET /api/races/{uid}/mark-columns =====

test('未認証ユーザーはレース印カラム一覧を取得できない', function () {
    // Arrange
    $race = createRaceForMarkColumnIndexTest();

    // Act
    $response = $this->getJson('/api/races/'.$race->uid.'/mark-columns');

    // Assert
    $response->assertUnauthorized();
});

test('認証済みユーザーは自分のレース印カラム一覧を取得できる', function () {
    // Arrange
    $user = User::factory()->create();
    $race = createRaceForMarkColumnIndexTest();

    // Act
    $response = $this->actingAs($user)->getJson('/api/races/'.$race->uid.'/mark-columns');

    // Assert
    $response->assertOk();
    $response->assertJsonStructure([
        'data' => [
            ['id', 'type', 'label', 'display_order'],
        ],
    ]);
});

test('初回一覧取得時に自分カテゴリのカラムが自動生成される', function () {
    // Arrange
    $user = User::factory()->create();
    $race = createRaceForMarkColumnIndexTest();

    // Act
    $response = $this->actingAs($user)->getJson('/api/races/'.$race->uid.'/mark-columns');

    // Assert
    $response->assertOk();
    $data = $response->json('data');
    expect($data)->not->toBeEmpty();
    expect(collect($data)->pluck('type'))->toContain('own');
});

test('印カラムは display_order 昇順で返る', function () {
    // Arrange
    $user = User::factory()->create();
    $race = createRaceForMarkColumnIndexTest();

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
            'label' => '友人B',
            'display_order' => 2,
        ]);
    RaceMarkColumn::factory()
        ->other()
        ->create([
            'race_id' => $race->id,
            'user_id' => $user->id,
            'label' => '友人A',
            'display_order' => 1,
        ]);

    // Act
    $response = $this->actingAs($user)->getJson('/api/races/'.$race->uid.'/mark-columns');

    // Assert
    $response->assertOk();
    $orders = collect($response->json('data'))->pluck('display_order')->all();
    expect($orders)->toBe([0, 1, 2]);
});

test('他ユーザーの印カラムは一覧に含まれない', function () {
    // Arrange
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $race = createRaceForMarkColumnIndexTest();

    RaceMarkColumn::factory()
        ->other()
        ->create([
            'race_id' => $race->id,
            'user_id' => $otherUser->id,
            'label' => '他人の列',
            'display_order' => 1,
        ]);

    // Act
    $response = $this->actingAs($user)->getJson('/api/races/'.$race->uid.'/mark-columns');

    // Assert
    $response->assertOk();
    $labels = collect($response->json('data'))->pluck('label')->all();
    expect($labels)->not->toContain('他人の列');
});

test('存在しないレースUIDで印カラム一覧を取得しようとすると404が返る', function () {
    // Arrange
    $user = User::factory()->create();

    // Act
    $response = $this->actingAs($user)->getJson('/api/races/non-existent-uid/mark-columns');

    // Assert
    $response->assertNotFound();
});
