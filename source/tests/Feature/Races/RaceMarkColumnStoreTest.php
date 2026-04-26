<?php

use App\Models\Race;
use App\Models\User;
use App\Models\Venue;
use Illuminate\Support\Facades\DB;

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

test('unauthenticated user cannot create other race mark column', function () {
    // Arrange
    $race = createRaceForMarkColumnStoreTest();

    // Act
    $response = $this->postJson('/api/races/'.$race->uid.'/mark-columns', [
        'label' => '友人A',
    ]);

    // Assert
    $response->assertUnauthorized();
});

test('authenticated user can add other mark column', function () {
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

test('other mark column can be created with empty label', function () {
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

test('display_order is appended to the end when creating new other column', function () {
    // Arrange
    $user = User::factory()->create();
    $race = createRaceForMarkColumnStoreTest();
    $now = now();

    DB::table('race_mark_columns')->insert([
        [
            'race_id' => $race->id,
            'user_id' => $user->id,
            'column_type' => 'own',
            'label' => null,
            'display_order' => 0,
            'created_at' => $now,
            'updated_at' => $now,
        ],
        [
            'race_id' => $race->id,
            'user_id' => $user->id,
            'column_type' => 'other',
            'label' => '友人A',
            'display_order' => 3,
            'created_at' => $now,
            'updated_at' => $now,
        ],
    ]);

    // Act
    $response = $this->actingAs($user)->postJson('/api/races/'.$race->uid.'/mark-columns', [
        'label' => '友人B',
    ]);

    // Assert
    $response->assertCreated();
    $response->assertJsonPath('data.display_order', 4);
});

test('label exceeding 32 characters returns 422', function () {
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

test('creating mark column for non-existent race uid returns 404', function () {
    // Arrange
    $user = User::factory()->create();

    // Act
    $response = $this->actingAs($user)->postJson('/api/races/non-existent-uid/mark-columns', [
        'label' => '友人A',
    ]);

    // Assert
    $response->assertNotFound();
});
