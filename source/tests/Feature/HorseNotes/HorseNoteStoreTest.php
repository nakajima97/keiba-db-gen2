<?php

use App\Models\Horse;
use App\Models\HorseNote;
use App\Models\Race;
use App\Models\User;
use App\Models\Venue;

/**
 * horse_notes store テスト用の race を作成して返す
 */
function createRaceForHorseNoteStoreTest(int $raceNumber = 2): Race
{
    $venue = Venue::firstOrCreate(['name' => '東京']);

    return Race::create([
        'venue_id' => $venue->id,
        'race_date' => '2026-04-26',
        'race_number' => $raceNumber,
    ]);
}

/**
 * horse_notes store テスト用の horse を作成して返す
 */
function createHorseForHorseNoteStoreTest(): Horse
{
    return Horse::create([
        'name' => 'ストアテスト用ホース'.uniqid(),
        'birth_year' => 2022,
    ]);
}

// ===== POST /api/horses/{horse}/notes =====

test('unauthenticated user cannot create horse note', function () {
    // Arrange
    $horse = createHorseForHorseNoteStoreTest();

    // Act
    $response = $this->postJson('/api/horses/'.$horse->id.'/notes', [
        'content' => 'テストメモ',
    ]);

    // Assert
    $response->assertUnauthorized();
});

test('authenticated user can create horse note with race_id', function () {
    // Arrange
    $user = User::factory()->create();
    $horse = createHorseForHorseNoteStoreTest();
    $race = createRaceForHorseNoteStoreTest();

    // Act
    $response = $this->actingAs($user)->postJson('/api/horses/'.$horse->id.'/notes', [
        'race_id' => $race->id,
        'content' => 'レース紐づきメモ',
    ]);

    // Assert
    $response->assertCreated();
    $response->assertJsonPath('data.horse_id', $horse->id);
    $response->assertJsonPath('data.race_id', $race->id);
    $response->assertJsonPath('data.content', 'レース紐づきメモ');
    $this->assertDatabaseHas('horse_notes', [
        'user_id' => $user->id,
        'horse_id' => $horse->id,
        'race_id' => $race->id,
        'content' => 'レース紐づきメモ',
    ]);
});

test('authenticated user can create horse note without race_id', function () {
    // Arrange
    $user = User::factory()->create();
    $horse = createHorseForHorseNoteStoreTest();

    // Act
    $response = $this->actingAs($user)->postJson('/api/horses/'.$horse->id.'/notes', [
        'content' => 'レース紐づきなしメモ',
    ]);

    // Assert
    $response->assertCreated();
    $response->assertJsonPath('data.horse_id', $horse->id);
    $response->assertJsonPath('data.race_id', null);
    $response->assertJsonPath('data.content', 'レース紐づきなしメモ');
    $this->assertDatabaseHas('horse_notes', [
        'user_id' => $user->id,
        'horse_id' => $horse->id,
        'race_id' => null,
        'content' => 'レース紐づきなしメモ',
    ]);
});

test('creating horse note for non-existent horse id returns 404', function () {
    // Arrange
    $user = User::factory()->create();

    // Act
    $response = $this->actingAs($user)->postJson('/api/horses/9999999/notes', [
        'content' => 'テストメモ',
    ]);

    // Assert
    $response->assertNotFound();
});

test('empty content returns 422', function () {
    // Arrange
    $user = User::factory()->create();
    $horse = createHorseForHorseNoteStoreTest();

    // Act
    $response = $this->actingAs($user)->postJson('/api/horses/'.$horse->id.'/notes', [
        'content' => '',
    ]);

    // Assert
    $response->assertUnprocessable();
    $response->assertJsonValidationErrors(['content']);
});

test('content exceeding 1000 characters returns 422', function () {
    // Arrange
    $user = User::factory()->create();
    $horse = createHorseForHorseNoteStoreTest();

    // Act
    $response = $this->actingAs($user)->postJson('/api/horses/'.$horse->id.'/notes', [
        'content' => str_repeat('あ', 1001),
    ]);

    // Assert
    $response->assertUnprocessable();
    $response->assertJsonValidationErrors(['content']);
});

test('missing content returns 422', function () {
    // Arrange
    $user = User::factory()->create();
    $horse = createHorseForHorseNoteStoreTest();

    // Act
    $response = $this->actingAs($user)->postJson('/api/horses/'.$horse->id.'/notes', []);

    // Assert
    $response->assertUnprocessable();
    $response->assertJsonValidationErrors(['content']);
});

test('duplicate note for same user, horse, and race_id returns 422', function () {
    // Arrange
    $user = User::factory()->create();
    $horse = createHorseForHorseNoteStoreTest();
    $race = createRaceForHorseNoteStoreTest();

    HorseNote::factory()->create([
        'user_id' => $user->id,
        'horse_id' => $horse->id,
        'race_id' => $race->id,
        'content' => '既存メモ',
    ]);

    // Act
    $response = $this->actingAs($user)->postJson('/api/horses/'.$horse->id.'/notes', [
        'race_id' => $race->id,
        'content' => '重複メモ',
    ]);

    // Assert
    $response->assertUnprocessable();
});

test('duplicate note for same user, horse, and null race_id returns 422', function () {
    // Arrange
    $user = User::factory()->create();
    $horse = createHorseForHorseNoteStoreTest();

    HorseNote::factory()->create([
        'user_id' => $user->id,
        'horse_id' => $horse->id,
        'race_id' => null,
        'content' => '既存メモ',
    ]);

    // Act
    $response = $this->actingAs($user)->postJson('/api/horses/'.$horse->id.'/notes', [
        'content' => '重複メモ',
    ]);

    // Assert
    $response->assertUnprocessable();
});
