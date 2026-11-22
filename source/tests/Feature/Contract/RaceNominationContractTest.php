<?php

use App\Models\Horse;
use App\Models\Race;
use App\Models\RaceNomination;
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
