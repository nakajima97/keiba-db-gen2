<?php

use App\Models\Horse;
use App\Models\Jockey;
use App\Models\Race;
use App\Models\RaceEntry;
use App\Models\User;
use App\Models\Venue;

/**
 * Arrange: race_entries update テスト用の Race + Horse + Jockey + RaceEntry を作成して RaceEntry を返す
 */
function createRaceEntryForUpdateTest(): RaceEntry
{
    $venue = Venue::firstOrCreate(['name' => '東京']);

    $race = Race::create([
        'venue_id' => $venue->id,
        'race_date' => '2026-04-18',
        'race_number' => 1,
    ]);

    $horse = Horse::create([
        'name' => '初期馬名'.uniqid(),
        'birth_year' => 2022,
    ]);

    $jockey = Jockey::create([
        'name' => '初期騎手名'.uniqid(),
    ]);

    return RaceEntry::create([
        'race_id' => $race->id,
        'horse_id' => $horse->id,
        'jockey_id' => $jockey->id,
        'frame_number' => 1,
        'horse_number' => 1,
        'weight' => 55.0,
        'horse_weight' => 480,
    ]);
}

/**
 * Arrange: テスト用の既存 Horse を作成して返す
 */
function createExistingHorseForUpdateTest(string $name): Horse
{
    return Horse::create([
        'name' => $name,
        'birth_year' => 2021,
    ]);
}

/**
 * Arrange: テスト用の既存 Jockey を作成して返す
 */
function createExistingJockeyForUpdateTest(string $name): Jockey
{
    return Jockey::create([
        'name' => $name,
    ]);
}

/**
 * 有効な更新ペイロードのデフォルト値
 *
 * @return array<string, mixed>
 */
function validRaceEntryUpdatePayload(array $overrides = []): array
{
    return array_merge([
        'horse_name' => 'コントレイル',
        'jockey_name' => 'ルメール',
        'frame_number' => 3,
        'horse_number' => 5,
        'weight' => '57.0',
        'horse_weight' => '486',
    ], $overrides);
}

// ===== PUT /races/{race:uid}/entries/{entry} =====

test('unauthenticated user is redirected when updating race entry', function () {
    // Arrange
    $entry = createRaceEntryForUpdateTest();

    // Act
    $response = $this->put(
        route('races.entries.update', ['race' => $entry->race->uid, 'entry' => $entry->id]),
        validRaceEntryUpdatePayload(),
    );

    // Assert
    $response->assertRedirect();
    $this->assertGuest();
});

test('authenticated user can update race entry with valid data', function () {
    // Arrange
    $user = User::factory()->create();
    $entry = createRaceEntryForUpdateTest();

    // Act
    $this->actingAs($user)->putJson(
        route('races.entries.update', ['race' => $entry->race->uid, 'entry' => $entry->id]),
        validRaceEntryUpdatePayload(),
    );

    // Assert
    $this->assertDatabaseHas('race_entries', [
        'id' => $entry->id,
        'frame_number' => 3,
        'horse_number' => 5,
        'weight' => 57.0,
        'horse_weight' => 486,
    ]);
});

test('updating with existing horse name reuses existing horse without creating new', function () {
    // Arrange
    $user = User::factory()->create();
    $entry = createRaceEntryForUpdateTest();
    $existingHorse = createExistingHorseForUpdateTest('コントレイル');
    $horseCountBefore = Horse::count();

    // Act
    $this->actingAs($user)->putJson(
        route('races.entries.update', ['race' => $entry->race->uid, 'entry' => $entry->id]),
        validRaceEntryUpdatePayload(['horse_name' => 'コントレイル']),
    );

    // Assert
    $this->assertDatabaseHas('race_entries', [
        'id' => $entry->id,
        'horse_id' => $existingHorse->id,
    ]);
    expect(Horse::count())->toBe($horseCountBefore);
});

test('updating with new horse name creates new horse record', function () {
    // Arrange
    $user = User::factory()->create();
    $entry = createRaceEntryForUpdateTest();
    $horseCountBefore = Horse::count();

    // Act
    $this->actingAs($user)->putJson(
        route('races.entries.update', ['race' => $entry->race->uid, 'entry' => $entry->id]),
        validRaceEntryUpdatePayload(['horse_name' => 'まったく新しい馬名']),
    );

    // Assert
    expect(Horse::count())->toBe($horseCountBefore + 1);
    $newHorse = Horse::where('name', 'まったく新しい馬名')->first();
    expect($newHorse)->not->toBeNull();
    $this->assertDatabaseHas('race_entries', [
        'id' => $entry->id,
        'horse_id' => $newHorse->id,
    ]);
});

test('updating with existing jockey name reuses existing jockey without creating new', function () {
    // Arrange
    $user = User::factory()->create();
    $entry = createRaceEntryForUpdateTest();
    $existingJockey = createExistingJockeyForUpdateTest('ルメール');
    $jockeyCountBefore = Jockey::count();

    // Act
    $this->actingAs($user)->putJson(
        route('races.entries.update', ['race' => $entry->race->uid, 'entry' => $entry->id]),
        validRaceEntryUpdatePayload(['jockey_name' => 'ルメール']),
    );

    // Assert
    $this->assertDatabaseHas('race_entries', [
        'id' => $entry->id,
        'jockey_id' => $existingJockey->id,
    ]);
    expect(Jockey::count())->toBe($jockeyCountBefore);
});

test('updating with new jockey name creates new jockey record', function () {
    // Arrange
    $user = User::factory()->create();
    $entry = createRaceEntryForUpdateTest();
    $jockeyCountBefore = Jockey::count();

    // Act
    $this->actingAs($user)->putJson(
        route('races.entries.update', ['race' => $entry->race->uid, 'entry' => $entry->id]),
        validRaceEntryUpdatePayload(['jockey_name' => 'まったく新しい騎手名']),
    );

    // Assert
    expect(Jockey::count())->toBe($jockeyCountBefore + 1);
    $newJockey = Jockey::where('name', 'まったく新しい騎手名')->first();
    expect($newJockey)->not->toBeNull();
    $this->assertDatabaseHas('race_entries', [
        'id' => $entry->id,
        'jockey_id' => $newJockey->id,
    ]);
});

test('horse_weight is optional and can be set to null', function () {
    // Arrange
    $user = User::factory()->create();
    $entry = createRaceEntryForUpdateTest();

    // Act
    $this->actingAs($user)->putJson(
        route('races.entries.update', ['race' => $entry->race->uid, 'entry' => $entry->id]),
        validRaceEntryUpdatePayload(['horse_weight' => '']),
    );

    // Assert
    $this->assertDatabaseHas('race_entries', [
        'id' => $entry->id,
        'horse_weight' => null,
    ]);
});

test('empty horse_name returns 422', function () {
    // Arrange
    $user = User::factory()->create();
    $entry = createRaceEntryForUpdateTest();

    // Act
    $response = $this->actingAs($user)->putJson(
        route('races.entries.update', ['race' => $entry->race->uid, 'entry' => $entry->id]),
        validRaceEntryUpdatePayload(['horse_name' => '']),
    );

    // Assert
    $response->assertUnprocessable();
    $response->assertJsonValidationErrors(['horse_name']);
});

test('empty jockey_name returns 422', function () {
    // Arrange
    $user = User::factory()->create();
    $entry = createRaceEntryForUpdateTest();

    // Act
    $response = $this->actingAs($user)->putJson(
        route('races.entries.update', ['race' => $entry->race->uid, 'entry' => $entry->id]),
        validRaceEntryUpdatePayload(['jockey_name' => '']),
    );

    // Assert
    $response->assertUnprocessable();
    $response->assertJsonValidationErrors(['jockey_name']);
});

test('frame_number out of range returns 422', function () {
    // Arrange
    $user = User::factory()->create();
    $entry = createRaceEntryForUpdateTest();

    // Act
    $response = $this->actingAs($user)->putJson(
        route('races.entries.update', ['race' => $entry->race->uid, 'entry' => $entry->id]),
        validRaceEntryUpdatePayload(['frame_number' => 9]),
    );

    // Assert
    $response->assertUnprocessable();
    $response->assertJsonValidationErrors(['frame_number']);
});

test('horse_number out of range returns 422', function () {
    // Arrange
    $user = User::factory()->create();
    $entry = createRaceEntryForUpdateTest();

    // Act
    $response = $this->actingAs($user)->putJson(
        route('races.entries.update', ['race' => $entry->race->uid, 'entry' => $entry->id]),
        validRaceEntryUpdatePayload(['horse_number' => 19]),
    );

    // Assert
    $response->assertUnprocessable();
    $response->assertJsonValidationErrors(['horse_number']);
});

test('empty weight returns 422', function () {
    // Arrange
    $user = User::factory()->create();
    $entry = createRaceEntryForUpdateTest();

    // Act
    $response = $this->actingAs($user)->putJson(
        route('races.entries.update', ['race' => $entry->race->uid, 'entry' => $entry->id]),
        validRaceEntryUpdatePayload(['weight' => '']),
    );

    // Assert
    $response->assertUnprocessable();
    $response->assertJsonValidationErrors(['weight']);
});

test('horse_number colliding with another entry in the same race returns 422', function () {
    // Arrange
    $user = User::factory()->create();
    $entry = createRaceEntryForUpdateTest();
    $anotherHorse = Horse::create([
        'name' => '別の馬'.uniqid(),
        'birth_year' => 2022,
    ]);
    $anotherJockey = Jockey::create([
        'name' => '別の騎手'.uniqid(),
    ]);
    RaceEntry::create([
        'race_id' => $entry->race_id,
        'horse_id' => $anotherHorse->id,
        'jockey_id' => $anotherJockey->id,
        'frame_number' => 4,
        'horse_number' => 7,
        'weight' => 56.0,
        'horse_weight' => 480,
    ]);

    // Act
    $response = $this->actingAs($user)->putJson(
        route('races.entries.update', ['race' => $entry->race->uid, 'entry' => $entry->id]),
        validRaceEntryUpdatePayload(['horse_number' => 7]),
    );

    // Assert
    $response->assertUnprocessable();
    $response->assertJsonValidationErrors([
        'horse_number' => '同じレース内でこの馬番は既に使われています。',
    ]);
});

test('non-existent entry id returns 404', function () {
    // Arrange
    $user = User::factory()->create();
    $entry = createRaceEntryForUpdateTest();

    // Act
    $response = $this->actingAs($user)->putJson(
        route('races.entries.update', ['race' => $entry->race->uid, 'entry' => 9999999]),
        validRaceEntryUpdatePayload(),
    );

    // Assert
    $response->assertNotFound();
});

test('non-existent race uid returns 404', function () {
    // Arrange
    $user = User::factory()->create();
    $entry = createRaceEntryForUpdateTest();

    // Act
    $response = $this->actingAs($user)->putJson(
        route('races.entries.update', ['race' => 'non-existent-uid', 'entry' => $entry->id]),
        validRaceEntryUpdatePayload(),
    );

    // Assert
    $response->assertNotFound();
});
