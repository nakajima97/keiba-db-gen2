<?php

use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * 1頭分のサンプルテキスト（JRA出馬表からコピーした形式）
 */
$sampleText = implode("\n", [
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
 * Arrange: venueを作成してIDを返す
 */
function createVenueForStoreTest(): int
{
    $now = now();
    DB::table('venues')->insert([
        'name' => '東京',
        'created_at' => $now,
        'updated_at' => $now,
    ]);

    return (int) DB::table('venues')->where('name', '東京')->value('id');
}

// ===== POST /races =====

test('unauthenticated user is redirected when posting to races store', function () use ($sampleText) {
    // Arrange
    $venueId = createVenueForStoreTest();

    // Act
    $response = $this->post(route('races.store'), [
        'venue_id' => $venueId,
        'race_date' => '2026-04-18',
        'race_number' => 1,
        'paste_text' => $sampleText,
    ]);

    // Assert
    $response->assertRedirect();
    $this->assertGuest();
});

test('valid post creates race, horses, jockeys and race_entries', function () use ($sampleText) {
    // Arrange
    $user = User::factory()->create();
    $venueId = createVenueForStoreTest();

    // Act
    $this->actingAs($user)->post(route('races.store'), [
        'venue_id' => $venueId,
        'race_date' => '2026-04-18',
        'race_number' => 1,
        'paste_text' => $sampleText,
    ]);

    // Assert
    $this->assertDatabaseCount('races', 1);
    $this->assertDatabaseHas('races', [
        'venue_id' => $venueId,
        'race_date' => '2026-04-18',
        'race_number' => 1,
    ]);

    $this->assertDatabaseCount('horses', 1);
    $this->assertDatabaseHas('horses', [
        'name' => 'エビスディアーナ',
        'birth_year' => 2023,
    ]);

    $this->assertDatabaseCount('jockeys', 1);
    $this->assertDatabaseHas('jockeys', [
        'name' => 'M.ディー',
    ]);

    $this->assertDatabaseCount('race_entries', 1);
    $this->assertDatabaseHas('race_entries', [
        'frame_number' => 1,
        'horse_number' => 1,
        'weight' => 55.0,
        'horse_weight' => 426,
    ]);
});

test('existing horse and jockey are reused and not duplicated', function () use ($sampleText) {
    // Arrange
    $user = User::factory()->create();
    $venueId = createVenueForStoreTest();
    $now = now();

    DB::table('horses')->insert([
        'name' => 'エビスディアーナ',
        'birth_year' => 2023,
        'created_at' => $now,
        'updated_at' => $now,
    ]);
    DB::table('jockeys')->insert([
        'name' => 'M.ディー',
        'created_at' => $now,
        'updated_at' => $now,
    ]);

    // Act
    $this->actingAs($user)->post(route('races.store'), [
        'venue_id' => $venueId,
        'race_date' => '2026-04-18',
        'race_number' => 1,
        'paste_text' => $sampleText,
    ]);

    // Assert
    $this->assertDatabaseCount('horses', 1);
    $this->assertDatabaseCount('jockeys', 1);
    $this->assertDatabaseCount('race_entries', 1);
});

test('successful post redirects back to races create page', function () use ($sampleText) {
    // Arrange
    $user = User::factory()->create();
    $venueId = createVenueForStoreTest();

    // Act
    $response = $this->actingAs($user)->post(route('races.store'), [
        'venue_id' => $venueId,
        'race_date' => '2026-04-18',
        'race_number' => 1,
        'paste_text' => $sampleText,
    ]);

    // Assert
    $response->assertRedirect(route('races.create'));
});

test('missing venue_id returns validation error', function () use ($sampleText) {
    // Arrange
    $user = User::factory()->create();

    // Act
    $response = $this->actingAs($user)->post(route('races.store'), [
        'race_date' => '2026-04-18',
        'race_number' => 1,
        'paste_text' => $sampleText,
    ]);

    // Assert
    $response->assertSessionHasErrors(['venue_id']);
});

test('missing race_date returns validation error', function () use ($sampleText) {
    // Arrange
    $user = User::factory()->create();
    $venueId = createVenueForStoreTest();

    // Act
    $response = $this->actingAs($user)->post(route('races.store'), [
        'venue_id' => $venueId,
        'race_number' => 1,
        'paste_text' => $sampleText,
    ]);

    // Assert
    $response->assertSessionHasErrors(['race_date']);
});

test('missing race_number returns validation error', function () use ($sampleText) {
    // Arrange
    $user = User::factory()->create();
    $venueId = createVenueForStoreTest();

    // Act
    $response = $this->actingAs($user)->post(route('races.store'), [
        'venue_id' => $venueId,
        'race_date' => '2026-04-18',
        'paste_text' => $sampleText,
    ]);

    // Assert
    $response->assertSessionHasErrors(['race_number']);
});

test('race_number out of range returns validation error', function () use ($sampleText) {
    // Arrange
    $user = User::factory()->create();
    $venueId = createVenueForStoreTest();

    // Act
    $response = $this->actingAs($user)->post(route('races.store'), [
        'venue_id' => $venueId,
        'race_date' => '2026-04-18',
        'race_number' => 13,
        'paste_text' => $sampleText,
    ]);

    // Assert
    $response->assertSessionHasErrors(['race_number']);
});

test('missing paste_text returns validation error', function () {
    // Arrange
    $user = User::factory()->create();
    $venueId = createVenueForStoreTest();

    // Act
    $response = $this->actingAs($user)->post(route('races.store'), [
        'venue_id' => $venueId,
        'race_date' => '2026-04-18',
        'race_number' => 1,
    ]);

    // Assert
    $response->assertSessionHasErrors(['paste_text']);
});

test('race_name ありで登録すると races テーブルに race_name が保存される', function () use ($sampleText) {
    // Arrange
    $user = User::factory()->create();
    $venueId = createVenueForStoreTest();

    // Act
    $this->actingAs($user)->post(route('races.store'), [
        'venue_id' => $venueId,
        'race_date' => '2026-04-18',
        'race_number' => 1,
        'paste_text' => $sampleText,
        'race_name' => '天皇賞（春）',
    ]);

    // Assert
    $this->assertDatabaseHas('races', ['race_name' => '天皇賞（春）']);
});

test('race_name を省略して登録すると races.race_name が null で保存される', function () use ($sampleText) {
    // Arrange
    $user = User::factory()->create();
    $venueId = createVenueForStoreTest();

    // Act
    $this->actingAs($user)->post(route('races.store'), [
        'venue_id' => $venueId,
        'race_date' => '2026-04-18',
        'race_number' => 1,
        'paste_text' => $sampleText,
    ]);

    // Assert
    $this->assertDatabaseHas('races', ['race_name' => null]);
});

test('race_number が 12 未満の場合、セッションの last_race_number は race_number + 1 になる', function () use ($sampleText) {
    // Arrange
    $user = User::factory()->create();
    $venueId = createVenueForStoreTest();

    // Act
    $response = $this->actingAs($user)->post(route('races.store'), [
        'venue_id' => $venueId,
        'race_date' => '2026-04-18',
        'race_number' => 11,
        'paste_text' => $sampleText,
    ]);

    // Assert
    $response->assertSessionHas('last_race_number', 12);
});

test('race_number が 12 の場合、セッションの last_race_number は 12 のまま（インクリメントされない）', function () use ($sampleText) {
    // Arrange
    $user = User::factory()->create();
    $venueId = createVenueForStoreTest();

    // Act
    $response = $this->actingAs($user)->post(route('races.store'), [
        'venue_id' => $venueId,
        'race_date' => '2026-04-18',
        'race_number' => 12,
        'paste_text' => $sampleText,
    ]);

    // Assert
    $response->assertSessionHas('last_race_number', 12);
});

test('duplicate race registration returns error', function () use ($sampleText) {
    // Arrange
    $user = User::factory()->create();
    $venueId = createVenueForStoreTest();
    $now = now();

    DB::table('races')->insert([
        'uid' => 'existing-uid-001',
        'venue_id' => $venueId,
        'race_date' => '2026-04-18',
        'race_number' => 1,
        'created_at' => $now,
        'updated_at' => $now,
    ]);

    // Act
    $response = $this->actingAs($user)->post(route('races.store'), [
        'venue_id' => $venueId,
        'race_date' => '2026-04-18',
        'race_number' => 1,
        'paste_text' => $sampleText,
    ]);

    // Assert
    $response->assertSessionHasErrors();
    $this->assertDatabaseCount('races', 1);
});
