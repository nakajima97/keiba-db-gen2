<?php

use App\Models\Horse;
use App\Models\Jockey;
use App\Models\Race;
use App\Models\RaceEntry;
use App\Models\User;
use App\Models\Venue;

/**
 * Arrange: race_entries 個別追加 POST テスト用の Race を作成して返す
 */
function createRaceForAddStoreTest(): Race
{
    $venue = Venue::firstOrCreate(['name' => '東京']);

    return Race::create([
        'venue_id' => $venue->id,
        'race_date' => '2026-04-18',
        'race_number' => 1,
    ]);
}

/**
 * Arrange: テスト用の既存 Horse を作成して返す
 */
function createExistingHorseForAddStoreTest(string $name): Horse
{
    return Horse::create([
        'name' => $name,
        'birth_year' => 2021,
    ]);
}

/**
 * Arrange: テスト用の既存 Jockey を作成して返す
 */
function createExistingJockeyForAddStoreTest(string $name): Jockey
{
    return Jockey::create([
        'name' => $name,
    ]);
}

/**
 * 有効な個別追加ペイロードのデフォルト値
 *
 * @return array<string, mixed>
 */
function validRaceEntryAddPayload(array $overrides = []): array
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

// ===== POST /races/{race:uid}/entries/add =====

test('未認証ユーザーが出馬登録を追加しようとするとリダイレクトされる', function () {
    // Arrange
    $race = createRaceForAddStoreTest();

    // Act
    $response = $this->post(
        "/races/{$race->uid}/entries/add",
        validRaceEntryAddPayload(),
    );

    // Assert
    $response->assertRedirect();
    $this->assertGuest();
});

test('認証済みユーザーは正常データで出馬登録を追加できる', function () {
    // Arrange
    $user = User::factory()->create();
    $race = createRaceForAddStoreTest();

    // Act
    $this->actingAs($user)->postJson(
        "/races/{$race->uid}/entries/add",
        validRaceEntryAddPayload(),
    );

    // Assert
    $this->assertDatabaseHas('race_entries', [
        'race_id' => $race->id,
        'frame_number' => 3,
        'horse_number' => 5,
        'weight' => 57.0,
        'horse_weight' => 486,
    ]);
});

test('既存の馬名で追加すると新規作成せず既存の馬を再利用する', function () {
    // Arrange
    $user = User::factory()->create();
    $race = createRaceForAddStoreTest();
    $existingHorse = createExistingHorseForAddStoreTest('コントレイル');
    $horseCountBefore = Horse::count();

    // Act
    $this->actingAs($user)->postJson(
        "/races/{$race->uid}/entries/add",
        validRaceEntryAddPayload(['horse_name' => 'コントレイル']),
    );

    // Assert
    $this->assertDatabaseHas('race_entries', [
        'race_id' => $race->id,
        'horse_id' => $existingHorse->id,
    ]);
    expect(Horse::count())->toBe($horseCountBefore);
});

test('新しい馬名で追加すると新規の馬レコードが作成される', function () {
    // Arrange
    $user = User::factory()->create();
    $race = createRaceForAddStoreTest();
    $horseCountBefore = Horse::count();

    // Act
    $this->actingAs($user)->postJson(
        "/races/{$race->uid}/entries/add",
        validRaceEntryAddPayload(['horse_name' => 'まったく新しい馬名']),
    );

    // Assert
    expect(Horse::count())->toBe($horseCountBefore + 1);
    $newHorse = Horse::where('name', 'まったく新しい馬名')->first();
    expect($newHorse)->not->toBeNull();
    $this->assertDatabaseHas('race_entries', [
        'race_id' => $race->id,
        'horse_id' => $newHorse->id,
    ]);
});

test('既存の騎手名で追加すると新規作成せず既存の騎手を再利用する', function () {
    // Arrange
    $user = User::factory()->create();
    $race = createRaceForAddStoreTest();
    $existingJockey = createExistingJockeyForAddStoreTest('ルメール');
    $jockeyCountBefore = Jockey::count();

    // Act
    $this->actingAs($user)->postJson(
        "/races/{$race->uid}/entries/add",
        validRaceEntryAddPayload(['jockey_name' => 'ルメール']),
    );

    // Assert
    $this->assertDatabaseHas('race_entries', [
        'race_id' => $race->id,
        'jockey_id' => $existingJockey->id,
    ]);
    expect(Jockey::count())->toBe($jockeyCountBefore);
});

test('新しい騎手名で追加すると新規の騎手レコードが作成される', function () {
    // Arrange
    $user = User::factory()->create();
    $race = createRaceForAddStoreTest();
    $jockeyCountBefore = Jockey::count();

    // Act
    $this->actingAs($user)->postJson(
        "/races/{$race->uid}/entries/add",
        validRaceEntryAddPayload(['jockey_name' => 'まったく新しい騎手名']),
    );

    // Assert
    expect(Jockey::count())->toBe($jockeyCountBefore + 1);
    $newJockey = Jockey::where('name', 'まったく新しい騎手名')->first();
    expect($newJockey)->not->toBeNull();
    $this->assertDatabaseHas('race_entries', [
        'race_id' => $race->id,
        'jockey_id' => $newJockey->id,
    ]);
});

test('追加時に horse_weight は省略可能で null として保存できる', function () {
    // Arrange
    $user = User::factory()->create();
    $race = createRaceForAddStoreTest();

    // Act
    $this->actingAs($user)->postJson(
        "/races/{$race->uid}/entries/add",
        validRaceEntryAddPayload(['horse_weight' => '']),
    );

    // Assert
    $this->assertDatabaseHas('race_entries', [
        'race_id' => $race->id,
        'horse_weight' => null,
    ]);
});

test('出馬登録追加成功時はレース詳細画面にリダイレクトされる', function () {
    // Arrange
    $user = User::factory()->create();
    $race = createRaceForAddStoreTest();

    // Act
    $response = $this->actingAs($user)->post(
        "/races/{$race->uid}/entries/add",
        validRaceEntryAddPayload(),
    );

    // Assert
    $response->assertRedirect(route('races.show', ['race' => $race->uid]));
});

test('追加時に horse_name が空のとき422が返る', function () {
    // Arrange
    $user = User::factory()->create();
    $race = createRaceForAddStoreTest();

    // Act
    $response = $this->actingAs($user)->postJson(
        "/races/{$race->uid}/entries/add",
        validRaceEntryAddPayload(['horse_name' => '']),
    );

    // Assert
    $response->assertUnprocessable();
    $response->assertJsonValidationErrors(['horse_name']);
});

test('追加時に jockey_name が空のとき422が返る', function () {
    // Arrange
    $user = User::factory()->create();
    $race = createRaceForAddStoreTest();

    // Act
    $response = $this->actingAs($user)->postJson(
        "/races/{$race->uid}/entries/add",
        validRaceEntryAddPayload(['jockey_name' => '']),
    );

    // Assert
    $response->assertUnprocessable();
    $response->assertJsonValidationErrors(['jockey_name']);
});

test('追加時に frame_number が範囲未満のとき422が返る', function () {
    // Arrange
    $user = User::factory()->create();
    $race = createRaceForAddStoreTest();

    // Act
    $response = $this->actingAs($user)->postJson(
        "/races/{$race->uid}/entries/add",
        validRaceEntryAddPayload(['frame_number' => 0]),
    );

    // Assert
    $response->assertUnprocessable();
    $response->assertJsonValidationErrors(['frame_number']);
});

test('追加時に frame_number が範囲超過のとき422が返る', function () {
    // Arrange
    $user = User::factory()->create();
    $race = createRaceForAddStoreTest();

    // Act
    $response = $this->actingAs($user)->postJson(
        "/races/{$race->uid}/entries/add",
        validRaceEntryAddPayload(['frame_number' => 9]),
    );

    // Assert
    $response->assertUnprocessable();
    $response->assertJsonValidationErrors(['frame_number']);
});

test('追加時に horse_number が範囲未満のとき422が返る', function () {
    // Arrange
    $user = User::factory()->create();
    $race = createRaceForAddStoreTest();

    // Act
    $response = $this->actingAs($user)->postJson(
        "/races/{$race->uid}/entries/add",
        validRaceEntryAddPayload(['horse_number' => 0]),
    );

    // Assert
    $response->assertUnprocessable();
    $response->assertJsonValidationErrors(['horse_number']);
});

test('追加時に horse_number が範囲超過のとき422が返る', function () {
    // Arrange
    $user = User::factory()->create();
    $race = createRaceForAddStoreTest();

    // Act
    $response = $this->actingAs($user)->postJson(
        "/races/{$race->uid}/entries/add",
        validRaceEntryAddPayload(['horse_number' => 19]),
    );

    // Assert
    $response->assertUnprocessable();
    $response->assertJsonValidationErrors(['horse_number']);
});

test('追加時に weight が空のとき422が返る', function () {
    // Arrange
    $user = User::factory()->create();
    $race = createRaceForAddStoreTest();

    // Act
    $response = $this->actingAs($user)->postJson(
        "/races/{$race->uid}/entries/add",
        validRaceEntryAddPayload(['weight' => '']),
    );

    // Assert
    $response->assertUnprocessable();
    $response->assertJsonValidationErrors(['weight']);
});

test('追加時に同じレース内の他エントリと horse_number が衝突するとき422が返る', function () {
    // Arrange
    $user = User::factory()->create();
    $race = createRaceForAddStoreTest();
    $existingHorse = Horse::create([
        'name' => '既存の馬'.uniqid(),
        'birth_year' => 2022,
    ]);
    $existingJockey = Jockey::create([
        'name' => '既存の騎手'.uniqid(),
    ]);
    RaceEntry::create([
        'race_id' => $race->id,
        'horse_id' => $existingHorse->id,
        'jockey_id' => $existingJockey->id,
        'frame_number' => 4,
        'horse_number' => 7,
        'weight' => 56.0,
        'horse_weight' => 480,
    ]);

    // Act
    $response = $this->actingAs($user)->postJson(
        "/races/{$race->uid}/entries/add",
        validRaceEntryAddPayload(['horse_number' => 7]),
    );

    // Assert
    $response->assertUnprocessable();
    $response->assertJsonValidationErrors([
        'horse_number' => '同じレース内でこの馬番は既に使われています。',
    ]);
});

test('追加保存時に存在しないレースUIDを指定すると404が返る', function () {
    // Arrange
    $user = User::factory()->create();

    // Act
    $response = $this->actingAs($user)->postJson(
        '/races/non-existent-uid/entries/add',
        validRaceEntryAddPayload(),
    );

    // Assert
    $response->assertNotFound();
});
