<?php

use App\Models\Horse;
use App\Models\Race;
use App\Models\User;

// ===== POST /api/races/{uid}/nominations =====

test('馬名を指定すると生年なしの競走馬が新規作成され登録馬に追加される', function () {
    // Arrange
    $user = User::factory()->create();
    $race = Race::factory()->create();

    // Act
    $response = $this->actingAs($user)->postJson('/api/races/'.$race->uid.'/nominations', [
        'horse_name' => '　ウワサノホース ',
    ]);

    // Assert
    $response->assertCreated();
    $horse = Horse::query()->where('name', 'ウワサノホース')->sole();
    expect($horse->birth_year)->toBeNull();
    $response->assertJsonPath('data.horse_id', $horse->id);
    $response->assertJsonPath('data.horse_uid', $horse->uid);
    $response->assertJsonPath('data.horse_name', 'ウワサノホース');
    $response->assertJsonPath('data.status', null);
    $response->assertJsonPath('data.note', null);
    $this->assertDatabaseHas('race_nominations', [
        'race_id' => $race->id,
        'horse_id' => $horse->id,
    ]);
});

test('馬名が既存の競走馬と一致する場合は既存の競走馬に紐付けて登録馬に追加される', function () {
    // Arrange
    $user = User::factory()->create();
    $race = Race::factory()->create();
    $horse = Horse::create(['name' => 'キゾンノホース', 'birth_year' => 2022]);

    // Act
    $response = $this->actingAs($user)->postJson('/api/races/'.$race->uid.'/nominations', [
        'horse_name' => 'キゾンノホース',
    ]);

    // Assert
    $response->assertCreated();
    $response->assertJsonPath('data.horse_id', $horse->id);
    expect(Horse::query()->where('name', 'キゾンノホース')->count())->toBe(1);
});

test('horse_id を指定すると候補から選んだ競走馬が登録馬に追加される', function () {
    // Arrange
    $user = User::factory()->create();
    $race = Race::factory()->create();
    Horse::create(['name' => 'ドウメイホース', 'birth_year' => 2020]);
    $selectedHorse = Horse::create(['name' => 'ドウメイホース', 'birth_year' => 2022]);

    // Act
    $response = $this->actingAs($user)->postJson('/api/races/'.$race->uid.'/nominations', [
        'horse_id' => $selectedHorse->id,
    ]);

    // Assert
    $response->assertCreated();
    $response->assertJsonPath('data.horse_id', $selectedHorse->id);
});

test('同じレースに同じ競走馬を重複して登録しようとすると422が返る', function () {
    // Arrange
    $user = User::factory()->create();
    $race = Race::factory()->create();
    $this->actingAs($user)->postJson('/api/races/'.$race->uid.'/nominations', [
        'horse_name' => 'ジュウフクホース',
    ])->assertCreated();

    // Act
    $response = $this->actingAs($user)->postJson('/api/races/'.$race->uid.'/nominations', [
        'horse_name' => 'ジュウフクホース',
    ]);

    // Assert
    $response->assertUnprocessable();
    $response->assertJsonValidationErrors(['horse_name']);
});
