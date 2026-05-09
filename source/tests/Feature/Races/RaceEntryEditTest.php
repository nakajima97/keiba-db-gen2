<?php

use App\Models\Horse;
use App\Models\Jockey;
use App\Models\Race;
use App\Models\RaceEntry;
use App\Models\User;
use App\Models\Venue;
use Inertia\Testing\AssertableInertia as Assert;

/**
 * Arrange: race_entries 編集画面テスト用の Race + Horse + Jockey + RaceEntry を作成して RaceEntry を返す
 */
function createRaceEntryForEditTest(): RaceEntry
{
    $venue = Venue::firstOrCreate(['name' => '東京']);

    $race = Race::create([
        'venue_id' => $venue->id,
        'race_date' => '2026-04-18',
        'race_number' => 5,
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
        'frame_number' => 2,
        'horse_number' => 4,
        'weight' => 56.0,
        'horse_weight' => 478,
    ]);
}

// ===== GET /races/{uid}/entries/{entry}/edit =====

test('未認証ユーザーが出馬登録編集画面にアクセスするとリダイレクトされる', function () {
    // Arrange
    $entry = createRaceEntryForEditTest();

    // Act
    $response = $this->get(route('races.entries.edit', ['race' => $entry->race->uid, 'entry' => $entry->id]));

    // Assert
    $response->assertRedirectToRoute('login');
});

test('認証済みユーザーは出馬登録編集画面にアクセスでき Inertia コンポーネントがレース情報と既存値付きで描画される', function () {
    // Arrange
    $user = User::factory()->create();
    $entry = createRaceEntryForEditTest();
    $entry->load(['race.venue', 'horse', 'jockey']);

    // Act
    $response = $this->actingAs($user)->get(
        route('races.entries.edit', ['race' => $entry->race->uid, 'entry' => $entry->id]),
    );

    // Assert
    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('races/entries/edit')
        ->where('race_uid', $entry->race->uid)
        ->where('entry_id', $entry->id)
        ->has('race_info', fn (Assert $raceInfo) => $raceInfo
            ->where('race_date', '2026-04-18')
            ->where('venue_name', '東京')
            ->where('race_number', 5)
            ->etc()
        )
        ->has('initial_values', fn (Assert $values) => $values
            ->where('horse_name', $entry->horse->name)
            ->where('jockey_name', $entry->jockey->name)
            ->where('frame_number', 2)
            ->where('horse_number', 4)
            ->where('weight', '56.0')
            ->where('horse_weight', '478')
            ->etc()
        )
    );
});

test('編集画面で存在しないエントリIDを指定すると404が返る', function () {
    // Arrange
    $user = User::factory()->create();
    $entry = createRaceEntryForEditTest();

    // Act
    $response = $this->actingAs($user)->get(
        route('races.entries.edit', ['race' => $entry->race->uid, 'entry' => 9999999]),
    );

    // Assert
    $response->assertNotFound();
});

test('編集画面で存在しないレースUIDを指定すると404が返る', function () {
    // Arrange
    $user = User::factory()->create();
    $entry = createRaceEntryForEditTest();

    // Act
    $response = $this->actingAs($user)->get(
        route('races.entries.edit', ['race' => 'non-existent-uid', 'entry' => $entry->id]),
    );

    // Assert
    $response->assertNotFound();
});
