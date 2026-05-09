<?php

use App\Models\Horse;
use App\Models\HorseNote;
use App\Models\Race;
use App\Models\User;
use App\Models\Venue;

/**
 * horse_notes index テスト用の race を作成して返す
 */
function createRaceForHorseNoteIndexTest(int $raceNumber = 1, ?string $raceName = null): Race
{
    $venue = Venue::firstOrCreate(['name' => '東京']);

    return Race::create([
        'venue_id' => $venue->id,
        'race_date' => '2026-04-26',
        'race_number' => $raceNumber,
        'race_name' => $raceName,
    ]);
}

/**
 * horse_notes index テスト用の horse を作成して返す
 */
function createHorseForHorseNoteIndexTest(): Horse
{
    return Horse::create([
        'name' => 'インデックステスト用ホース'.uniqid(),
        'birth_year' => 2022,
    ]);
}

// ===== GET /api/horses/{horse}/notes =====

test('未認証ユーザーは馬メモ一覧を取得できない', function () {
    // Arrange
    $horse = createHorseForHorseNoteIndexTest();

    // Act
    $response = $this->getJson('/api/horses/'.$horse->id.'/notes');

    // Assert
    $response->assertUnauthorized();
});

test('認証済みユーザーは自分の馬メモ一覧を updated_at の降順で取得できる', function () {
    // Arrange
    $user = User::factory()->create();
    $horse = createHorseForHorseNoteIndexTest();
    $race = createRaceForHorseNoteIndexTest(1, '皐月賞');

    HorseNote::factory()->create([
        'user_id' => $user->id,
        'horse_id' => $horse->id,
        'race_id' => $race->id,
        'content' => 'レース紐づきありメモ',
        'updated_at' => '2026-04-25 10:00:00',
    ]);
    HorseNote::factory()->create([
        'user_id' => $user->id,
        'horse_id' => $horse->id,
        'race_id' => null,
        'content' => 'レース紐づきなしメモ',
        'updated_at' => '2026-04-27 10:00:00',
    ]);

    // Act
    $response = $this->actingAs($user)->getJson('/api/horses/'.$horse->id.'/notes');

    // Assert
    $response->assertOk();
    $contents = collect($response->json('data'))->pluck('content')->all();
    expect($contents)->toBe(['レース紐づきなしメモ', 'レース紐づきありメモ']);
});

test('レースに紐づく馬メモにはレース詳細が含まれる', function () {
    // Arrange
    $user = User::factory()->create();
    $horse = createHorseForHorseNoteIndexTest();
    $race = createRaceForHorseNoteIndexTest(11, '皐月賞');

    HorseNote::factory()->create([
        'user_id' => $user->id,
        'horse_id' => $horse->id,
        'race_id' => $race->id,
        'content' => 'レース紐づきありメモ',
    ]);

    // Act
    $response = $this->actingAs($user)->getJson('/api/horses/'.$horse->id.'/notes');

    // Assert
    $response->assertOk();
    $response->assertJsonPath('data.0.race.uid', $race->uid);
    $response->assertJsonPath('data.0.race.race_date', '2026-04-26');
    $response->assertJsonPath('data.0.race.venue_name', '東京');
    $response->assertJsonPath('data.0.race.race_number', 11);
    $response->assertJsonPath('data.0.race.race_name', '皐月賞');
});

test('レース未紐付けの馬メモは race が null で返る', function () {
    // Arrange
    $user = User::factory()->create();
    $horse = createHorseForHorseNoteIndexTest();

    HorseNote::factory()->create([
        'user_id' => $user->id,
        'horse_id' => $horse->id,
        'race_id' => null,
        'content' => 'レース紐づきなしメモ',
    ]);

    // Act
    $response = $this->actingAs($user)->getJson('/api/horses/'.$horse->id.'/notes');

    // Assert
    $response->assertOk();
    $response->assertJsonPath('data.0.race', null);
});

test('他ユーザーの馬メモは一覧に含まれない', function () {
    // Arrange
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $horse = createHorseForHorseNoteIndexTest();

    HorseNote::factory()->create([
        'user_id' => $otherUser->id,
        'horse_id' => $horse->id,
        'race_id' => null,
        'content' => '他人のメモ',
    ]);

    // Act
    $response = $this->actingAs($user)->getJson('/api/horses/'.$horse->id.'/notes');

    // Assert
    $response->assertOk();
    $contents = collect($response->json('data'))->pluck('content')->all();
    expect($contents)->not->toContain('他人のメモ');
});

test('存在しない馬IDで馬メモ一覧を取得しようとすると404が返る', function () {
    // Arrange
    $user = User::factory()->create();

    // Act
    $response = $this->actingAs($user)->getJson('/api/horses/9999999/notes');

    // Assert
    $response->assertNotFound();
});
