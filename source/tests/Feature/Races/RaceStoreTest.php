<?php

use App\Models\Trainer;
use App\Models\User;
use App\Support\NanoId;
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

test('未認証ユーザーがレース store にPOSTするとリダイレクトされる', function () use ($sampleText) {
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

test('正常POSTで race・horses・jockeys・race_entries が作成される', function () use ($sampleText) {
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

test('正常POSTで調教師マスタが作成され race_entries に紐づく', function () use ($sampleText) {
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
    $this->assertDatabaseCount('trainers', 1);
    $trainer = Trainer::query()->firstOrFail();
    expect($trainer->name)->toBe('加藤 征弘')
        ->and($trainer->affiliation)->toBe('美浦');
    $this->assertDatabaseHas('race_entries', ['trainer_id' => $trainer->id]);
});

test('既存の馬と騎手は再利用され重複登録されない', function () use ($sampleText) {
    // Arrange
    $user = User::factory()->create();
    $venueId = createVenueForStoreTest();
    $now = now();

    DB::table('horses')->insert([
        'uid' => NanoId::generate(),
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

test('POST成功時はレース作成画面に戻る', function () use ($sampleText) {
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

test('venue_id が未指定のときバリデーションエラーが返る', function () use ($sampleText) {
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

test('race_date が未指定のときバリデーションエラーが返る', function () use ($sampleText) {
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

test('race_number が未指定のときバリデーションエラーが返る', function () use ($sampleText) {
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

test('race_number が範囲外のときバリデーションエラーが返る', function () use ($sampleText) {
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

test('paste_text が未指定のとき出走馬なしでレースのみが作成される', function () {
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
    $response->assertSessionHasNoErrors();
    $this->assertDatabaseCount('races', 1);
    $this->assertDatabaseHas('races', [
        'venue_id' => $venueId,
        'race_number' => 1,
    ]);
    $this->assertDatabaseCount('race_entries', 0);
    $this->assertDatabaseCount('horses', 0);
});

test('paste_text が空白のみのとき出走馬なしでレースのみが作成される', function () {
    // Arrange
    $user = User::factory()->create();
    $venueId = createVenueForStoreTest();

    // Act
    $response = $this->actingAs($user)->post(route('races.store'), [
        'venue_id' => $venueId,
        'race_date' => '2026-04-18',
        'race_number' => 1,
        'paste_text' => "  \n\t  \n",
    ]);

    // Assert
    $response->assertSessionHasNoErrors();
    $this->assertDatabaseCount('races', 1);
    $this->assertDatabaseCount('race_entries', 0);
});

test('出馬表なしで登録するとレース詳細画面へリダイレクトされる', function () {
    // Arrange
    $user = User::factory()->create();
    $venueId = createVenueForStoreTest();

    // Act
    $response = $this->actingAs($user)->post(route('races.store'), [
        'venue_id' => $venueId,
        'race_date' => '2026-04-18',
        'race_number' => 1,
        'paste_text' => '',
    ]);

    // Assert
    $uid = (string) DB::table('races')->value('uid');
    $response->assertRedirect(route('races.show', ['race' => $uid]));
});

test('出馬表なしで登録してもレース名は保存される', function () {
    // Arrange
    $user = User::factory()->create();
    $venueId = createVenueForStoreTest();

    // Act
    $this->actingAs($user)->post(route('races.store'), [
        'venue_id' => $venueId,
        'race_date' => '2026-04-18',
        'race_number' => 11,
        'race_name' => '天皇賞（秋）',
    ]);

    // Assert
    $this->assertDatabaseHas('races', [
        'race_number' => 11,
        'race_name' => '天皇賞（秋）',
    ]);
});

test('出馬表を入力したが形式を認識できないときはエラーになりレースは作成されない', function () {
    // Arrange
    $user = User::factory()->create();
    $venueId = createVenueForStoreTest();

    // Act
    $response = $this->actingAs($user)->post(route('races.store'), [
        'venue_id' => $venueId,
        'race_date' => '2026-04-18',
        'race_number' => 1,
        'paste_text' => '出馬表ではないテキスト',
    ]);

    // Assert
    $response->assertSessionHasErrors(['paste_text' => '出馬表の形式が認識できません。']);
    $this->assertDatabaseCount('races', 0);
});

test('出馬表なしでも同じ競馬場・レース日・レース番号のレースがあれば重複エラーになる', function () {
    // Arrange
    $user = User::factory()->create();
    $venueId = createVenueForStoreTest();
    $now = now();
    DB::table('races')->insert([
        'uid' => 'existing-uid-002',
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
    ]);

    // Assert
    $response->assertSessionHasErrors();
    $this->assertDatabaseCount('races', 1);
});

test('出馬表ありで登録すると生年未設定の同名馬（登録馬）に生年を補完して再利用する', function () use ($sampleText) {
    // Arrange
    $user = User::factory()->create();
    $venueId = createVenueForStoreTest();
    $horseId = DB::table('horses')->insertGetId([
        'uid' => NanoId::generate(),
        'name' => 'エビスディアーナ',
        'birth_year' => null,
        'created_at' => now(),
        'updated_at' => now(),
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
    $this->assertDatabaseHas('horses', ['id' => $horseId, 'birth_year' => 2023]);
    $this->assertDatabaseHas('race_entries', ['horse_id' => $horseId]);
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

test('レースを重複登録しようとするとエラーが返る', function () use ($sampleText) {
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
