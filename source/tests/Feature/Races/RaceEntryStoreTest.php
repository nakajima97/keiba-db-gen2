<?php

use App\Models\Race;
use App\Models\User;
use App\Models\Venue;
use Illuminate\Support\Facades\DB;

/**
 * 1頭分のサンプルテキスト（JRA出馬表からコピーした形式）
 */
$entryStoreSampleText = implode("\n", [
    '枠1白	1	',
    'エビスディアーナ	',
    '127.8',
    '(11番人気)',
    '426kg(-2)',
    '加藤 晃央',
    '',
    '恵比寿牧場',
    '',
    '加藤 征弘(美浦)',
    '',
    '父：マジェスティックウォリアー',
    '母：エビスオール',
    '(母の父：Chief Seattle)',
    '勝負服の画像',
    '',
    '牝3/黒鹿',
    '',
    '55.0kg',
    '',
    'M.ディー',
]);

/**
 * Arrange: race_entries store テスト用の venue と race を作成して race を返す
 */
function createRaceForEntryTest(): Race
{
    $venue = Venue::firstOrCreate(['name' => '東京']);

    return Race::create([
        'venue_id' => $venue->id,
        'race_date' => '2026-04-18',
        'race_number' => 1,
    ]);
}

// ===== POST /races/{uid}/entries =====

test('unauthenticated user is redirected when posting to race entries store', function () use ($entryStoreSampleText) {
    // Arrange
    $race = createRaceForEntryTest();

    // Act
    $response = $this->post(route('races.entries.store', ['race' => $race->uid]), [
        'paste_text' => $entryStoreSampleText,
    ]);

    // Assert
    $response->assertRedirect();
    $this->assertGuest();
});

test('valid paste text creates race_entries, horses and jockeys with correct count', function () use ($entryStoreSampleText) {
    // Arrange
    $user = User::factory()->create();
    $race = createRaceForEntryTest();

    // Act
    $this->actingAs($user)->post(route('races.entries.store', ['race' => $race->uid]), [
        'paste_text' => $entryStoreSampleText,
    ]);

    // Assert
    $this->assertDatabaseCount('race_entries', 1);
    $this->assertDatabaseHas('race_entries', [
        'race_id' => $race->id,
        'frame_number' => 1,
        'horse_number' => 1,
        'weight' => 55.0,
        'horse_weight' => 426,
    ]);

    $this->assertDatabaseCount('horses', 1);
    $this->assertDatabaseHas('horses', [
        'name' => 'エビスディアーナ',
    ]);

    $this->assertDatabaseCount('jockeys', 1);
    $this->assertDatabaseHas('jockeys', [
        'name' => 'M.ディー',
    ]);
});

test('existing horse is reused and not duplicated on race entry store', function () use ($entryStoreSampleText) {
    // Arrange
    $user = User::factory()->create();
    $race = createRaceForEntryTest();
    $now = now();

    DB::table('horses')->insert([
        'name' => 'エビスディアーナ',
        'birth_year' => 2023,
        'created_at' => $now,
        'updated_at' => $now,
    ]);

    // Act
    $this->actingAs($user)->post(route('races.entries.store', ['race' => $race->uid]), [
        'paste_text' => $entryStoreSampleText,
    ]);

    // Assert
    $this->assertDatabaseCount('horses', 1);
    $this->assertDatabaseCount('race_entries', 1);
});

test('existing jockey is reused and not duplicated on race entry store', function () use ($entryStoreSampleText) {
    // Arrange
    $user = User::factory()->create();
    $race = createRaceForEntryTest();
    $now = now();

    DB::table('jockeys')->insert([
        'name' => 'M.ディー',
        'created_at' => $now,
        'updated_at' => $now,
    ]);

    // Act
    $this->actingAs($user)->post(route('races.entries.store', ['race' => $race->uid]), [
        'paste_text' => $entryStoreSampleText,
    ]);

    // Assert
    $this->assertDatabaseCount('jockeys', 1);
    $this->assertDatabaseCount('race_entries', 1);
});

test('successful race entry store redirects to races show', function () use ($entryStoreSampleText) {
    // Arrange
    $user = User::factory()->create();
    $race = createRaceForEntryTest();

    // Act
    $response = $this->actingAs($user)->post(route('races.entries.store', ['race' => $race->uid]), [
        'paste_text' => $entryStoreSampleText,
    ]);

    // Assert
    $response->assertRedirect(route('races.show', ['race' => $race->uid]));
});

test('empty paste_text returns validation error on race entry store', function () {
    // Arrange
    $user = User::factory()->create();
    $race = createRaceForEntryTest();

    // Act
    $response = $this->actingAs($user)->post(route('races.entries.store', ['race' => $race->uid]), [
        'paste_text' => '',
    ]);

    // Assert
    $response->assertSessionHasErrors(['paste_text']);
});

test('invalid paste_text format returns session errors on race entry store', function () {
    // Arrange
    $user = User::factory()->create();
    $race = createRaceForEntryTest();

    // Act
    $response = $this->actingAs($user)->post(route('races.entries.store', ['race' => $race->uid]), [
        'paste_text' => '不正なフォーマットのテキスト',
    ]);

    // Assert
    $response->assertSessionHasErrors();
});
