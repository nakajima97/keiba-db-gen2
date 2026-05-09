<?php

use App\Models\Race;
use App\Models\User;
use App\Models\Venue;
use Inertia\Testing\AssertableInertia as Assert;

/**
 * Arrange: race_entries 個別追加画面テスト用の Race を作成して返す
 */
function createRaceForAddTest(): Race
{
    $venue = Venue::firstOrCreate(['name' => '東京']);

    return Race::create([
        'venue_id' => $venue->id,
        'race_date' => '2026-04-18',
        'race_number' => 5,
    ]);
}

// ===== GET /races/{uid}/entries/add =====

test('未認証ユーザーが出馬登録追加画面にアクセスするとリダイレクトされる', function () {
    // Arrange
    $race = createRaceForAddTest();

    // Act
    $response = $this->get("/races/{$race->uid}/entries/add");

    // Assert
    $response->assertRedirectToRoute('login');
});

test('認証済みユーザーは出馬登録追加画面にアクセスでき Inertia コンポーネントがレース情報付きで描画される', function () {
    // Arrange
    $user = User::factory()->create();
    $race = createRaceForAddTest();

    // Act
    $response = $this->actingAs($user)->get("/races/{$race->uid}/entries/add");

    // Assert
    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('races/entries/add')
        ->where('race_uid', $race->uid)
        ->has('race_info', fn (Assert $raceInfo) => $raceInfo
            ->where('race_date', '2026-04-18')
            ->where('venue_name', '東京')
            ->where('race_number', 5)
            ->etc()
        )
    );
});

test('追加画面で存在しないレースUIDを指定すると404が返る', function () {
    // Arrange
    $user = User::factory()->create();

    // Act
    $response = $this->actingAs($user)->get('/races/non-existent-uid/entries/add');

    // Assert
    $response->assertNotFound();
});
