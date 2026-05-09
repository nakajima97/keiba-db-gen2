<?php

use App\Models\Horse;
use App\Models\HorseNote;
use App\Models\Jockey;
use App\Models\Race;
use App\Models\RaceEntry;
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

/**
 * 指定したレースに馬が出走している状態を作る（race_entry を作成）
 */
function entryHorseForHorseNoteStoreTest(Race $race, Horse $horse, int $horseNumber = 1): RaceEntry
{
    $jockey = Jockey::create(['name' => 'テスト騎手'.uniqid()]);

    return RaceEntry::create([
        'race_id' => $race->id,
        'horse_id' => $horse->id,
        'jockey_id' => $jockey->id,
        'frame_number' => 1,
        'horse_number' => $horseNumber,
        'weight' => 56.0,
    ]);
}

// ===== POST /api/horses/{horse}/notes =====

test('未認証ユーザーは馬メモを作成できない', function () {
    // Arrange
    $horse = createHorseForHorseNoteStoreTest();

    // Act
    $response = $this->postJson('/api/horses/'.$horse->id.'/notes', [
        'content' => 'テストメモ',
    ]);

    // Assert
    $response->assertUnauthorized();
});

test('認証済みユーザーは race_id 付きの馬メモを作成できる', function () {
    // Arrange
    $user = User::factory()->create();
    $horse = createHorseForHorseNoteStoreTest();
    $race = createRaceForHorseNoteStoreTest();
    entryHorseForHorseNoteStoreTest($race, $horse);

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

test('認証済みユーザーは race_id なしで馬メモを作成できる', function () {
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

test('存在しない馬IDで馬メモを作成しようとすると404が返る', function () {
    // Arrange
    $user = User::factory()->create();

    // Act
    $response = $this->actingAs($user)->postJson('/api/horses/9999999/notes', [
        'content' => 'テストメモ',
    ]);

    // Assert
    $response->assertNotFound();
});

test('本文が空のとき422が返る', function () {
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

test('本文が1000文字を超えると422が返る', function () {
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

test('本文が未指定のとき422が返る', function () {
    // Arrange
    $user = User::factory()->create();
    $horse = createHorseForHorseNoteStoreTest();

    // Act
    $response = $this->actingAs($user)->postJson('/api/horses/'.$horse->id.'/notes', []);

    // Assert
    $response->assertUnprocessable();
    $response->assertJsonValidationErrors(['content']);
});

test('同じユーザー・馬・race_id の馬メモが既に存在するとき422が返る', function () {
    // Arrange
    $user = User::factory()->create();
    $horse = createHorseForHorseNoteStoreTest();
    $race = createRaceForHorseNoteStoreTest();
    entryHorseForHorseNoteStoreTest($race, $horse);

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

test('対象馬が出走していない race_id で馬メモを作成しようとすると422が返る', function () {
    // Arrange
    $user = User::factory()->create();
    $horse = createHorseForHorseNoteStoreTest();
    $race = createRaceForHorseNoteStoreTest();
    // 出走情報を作らないまま race_id を指定する

    // Act
    $response = $this->actingAs($user)->postJson('/api/horses/'.$horse->id.'/notes', [
        'race_id' => $race->id,
        'content' => '出走していないレースに紐づくメモ',
    ]);

    // Assert
    $response->assertUnprocessable();
    $response->assertJsonValidationErrors(['race_id']);
});

test('同じユーザー・馬で race_id が null の馬メモが既に存在するとき422が返る', function () {
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
