<?php

use App\Models\Horse;
use App\Models\Race;
use App\Models\RaceMarkColumn;
use App\Models\RaceNomination;
use App\Models\RaceNominationColumnMemo;
use App\Models\User;
use Spectator\Spectator;

beforeEach(function () {
    Spectator::using('openapi.yaml');
});

// ===== POST /api/races/{uid}/nominations =====

test('契約: POST createRaceNomination が OpenAPI 仕様と一致する', function () {
    // Arrange
    $user = User::factory()->create();
    $race = Race::factory()->create();

    // Act
    $response = $this->actingAs($user)
        ->postJson('/api/races/'.$race->uid.'/nominations', ['horse_name' => '契約テスト登録馬']);

    // Assert
    $response->assertValidRequest()->assertValidResponse(201);
});

// ===== DELETE /api/races/{uid}/nominations/{nomination_id} =====

test('契約: DELETE deleteRaceNomination が OpenAPI 仕様と一致する', function () {
    // Arrange
    $user = User::factory()->create();
    $nomination = RaceNomination::factory()->create();

    // Act
    $response = $this->actingAs($user)
        ->deleteJson('/api/races/'.$nomination->race->uid.'/nominations/'.$nomination->id);

    // Assert
    $response->assertValidRequest()->assertValidResponse(204);
});

// ===== PUT /api/races/{uid}/nominations/{nomination_id}/mark =====

test('契約: PUT putRaceNominationMark が OpenAPI 仕様と一致する', function () {
    // Arrange
    $user = User::factory()->create();
    $nomination = RaceNomination::factory()->create();

    // Act
    $response = $this->actingAs($user)
        ->putJson('/api/races/'.$nomination->race->uid.'/nominations/'.$nomination->id.'/mark', ['status' => 'check']);

    // Assert
    $response->assertValidRequest()->assertValidResponse(200);
});

// ===== PUT /api/races/{uid}/mark-columns/{column_id}/nominations/{nomination_id}/mark =====

test('契約: PUT putRaceNominationColumnMark が OpenAPI 仕様と一致する', function () {
    // Arrange
    $user = User::factory()->create();
    $nomination = RaceNomination::factory()->create();
    $column = RaceMarkColumn::factory()->other()->create([
        'race_id' => $nomination->race_id,
        'user_id' => $user->id,
    ]);

    // Act
    $response = $this->actingAs($user)
        ->putJson('/api/races/'.$nomination->race->uid.'/mark-columns/'.$column->id.'/nominations/'.$nomination->id.'/mark', ['mark_value' => '◎']);

    // Assert
    $response->assertValidRequest()->assertValidResponse(200);
});

// ===== PUT /api/races/{uid}/mark-columns/{column_id}/nominations/{nomination_id}/memo =====

test('契約: PUT putRaceNominationColumnMemo が OpenAPI 仕様と一致する', function () {
    // Arrange
    $user = User::factory()->create();
    $nomination = RaceNomination::factory()->create();
    $column = RaceMarkColumn::factory()->other()->create([
        'race_id' => $nomination->race_id,
        'user_id' => $user->id,
    ]);

    // Act
    $response = $this->actingAs($user)
        ->putJson('/api/races/'.$nomination->race->uid.'/mark-columns/'.$column->id.'/nominations/'.$nomination->id.'/memo', ['content' => '契約テスト']);

    // Assert
    $response->assertValidRequest()->assertValidResponse(201);
});

// ===== DELETE /api/races/{uid}/mark-columns/{column_id}/nominations/{nomination_id}/memo =====

test('契約: DELETE deleteRaceNominationColumnMemo が OpenAPI 仕様と一致する', function () {
    // Arrange
    $user = User::factory()->create();
    $nomination = RaceNomination::factory()->create();
    $column = RaceMarkColumn::factory()->other()->create([
        'race_id' => $nomination->race_id,
        'user_id' => $user->id,
    ]);
    RaceNominationColumnMemo::factory()->create([
        'race_mark_column_id' => $column->id,
        'race_nomination_id' => $nomination->id,
    ]);

    // Act
    $response = $this->actingAs($user)
        ->deleteJson('/api/races/'.$nomination->race->uid.'/mark-columns/'.$column->id.'/nominations/'.$nomination->id.'/memo');

    // Assert
    $response->assertValidRequest()->assertValidResponse(204);
});

// ===== GET /api/horses/search =====

test('契約: GET searchHorses が OpenAPI 仕様と一致する', function () {
    // Arrange
    $user = User::factory()->create();
    Horse::create(['name' => '契約テスト検索馬', 'birth_year' => null]);

    // Act
    $response = $this->actingAs($user)->getJson('/api/horses/search?keyword=契約テスト');

    // Assert
    $response->assertValidRequest()->assertValidResponse(200);
});
