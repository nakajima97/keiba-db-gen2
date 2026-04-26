<?php

use App\Models\Race;
use App\Models\User;
use App\Models\Venue;
use Illuminate\Support\Facades\DB;

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

test('unauthenticated user cannot list race mark columns', function () {
    // Arrange
    $race = createRaceForMarkColumnIndexTest();

    // Act
    $response = $this->getJson('/api/races/'.$race->uid.'/mark-columns');

    // Assert
    $response->assertUnauthorized();
});

test('authenticated user can list own race mark columns', function () {
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

test('own column is auto-generated when listing for the first time', function () {
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

test('mark columns are returned in display_order ascending order', function () {
    // Arrange
    $user = User::factory()->create();
    $race = createRaceForMarkColumnIndexTest();
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
            'label' => '友人B',
            'display_order' => 2,
            'created_at' => $now,
            'updated_at' => $now,
        ],
        [
            'race_id' => $race->id,
            'user_id' => $user->id,
            'column_type' => 'other',
            'label' => '友人A',
            'display_order' => 1,
            'created_at' => $now,
            'updated_at' => $now,
        ],
    ]);

    // Act
    $response = $this->actingAs($user)->getJson('/api/races/'.$race->uid.'/mark-columns');

    // Assert
    $response->assertOk();
    $orders = collect($response->json('data'))->pluck('display_order')->all();
    expect($orders)->toBe([0, 1, 2]);
});

test('other users mark columns are not included in the list', function () {
    // Arrange
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $race = createRaceForMarkColumnIndexTest();
    $now = now();

    DB::table('race_mark_columns')->insert([
        'race_id' => $race->id,
        'user_id' => $otherUser->id,
        'column_type' => 'other',
        'label' => '他人の列',
        'display_order' => 1,
        'created_at' => $now,
        'updated_at' => $now,
    ]);

    // Act
    $response = $this->actingAs($user)->getJson('/api/races/'.$race->uid.'/mark-columns');

    // Assert
    $response->assertOk();
    $labels = collect($response->json('data'))->pluck('label')->all();
    expect($labels)->not->toContain('他人の列');
});

test('listing mark columns for non-existent race uid returns 404', function () {
    // Arrange
    $user = User::factory()->create();

    // Act
    $response = $this->actingAs($user)->getJson('/api/races/non-existent-uid/mark-columns');

    // Assert
    $response->assertNotFound();
});
