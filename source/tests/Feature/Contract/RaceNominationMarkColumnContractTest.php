<?php

use App\Models\Race;
use App\Models\RaceNominationMarkColumn;
use App\Models\User;
use Spectator\Spectator;

beforeEach(function () {
    Spectator::using('openapi.yaml');
});

// ===== GET /api/races/{uid}/nomination-mark-columns =====

test('契約: GET listRaceNominationMarkColumns が OpenAPI 仕様と一致する', function () {
    // Arrange
    $user = User::factory()->create();
    $race = Race::factory()->create();
    RaceNominationMarkColumn::factory()->create([
        'race_id' => $race->id,
        'user_id' => $user->id,
    ]);

    // Act
    $response = $this->actingAs($user)
        ->getJson('/api/races/'.$race->uid.'/nomination-mark-columns');

    // Assert
    $response->assertValidRequest()->assertValidResponse(200);
});

// ===== POST /api/races/{uid}/nomination-mark-columns =====

test('契約: POST createRaceNominationMarkColumn が OpenAPI 仕様と一致する', function () {
    // Arrange
    $user = User::factory()->create();
    $race = Race::factory()->create();

    // Act
    $response = $this->actingAs($user)
        ->postJson('/api/races/'.$race->uid.'/nomination-mark-columns', [
            'label' => '友人A',
        ]);

    // Assert
    $response->assertValidRequest()->assertValidResponse(201);
});

// ===== PATCH /api/races/{uid}/nomination-mark-columns/{id} =====

test('契約: PATCH updateRaceNominationMarkColumnLabel が OpenAPI 仕様と一致する', function () {
    // Arrange
    $user = User::factory()->create();
    $race = Race::factory()->create();
    $column = RaceNominationMarkColumn::factory()->create([
        'race_id' => $race->id,
        'user_id' => $user->id,
    ]);

    // Act
    $response = $this->actingAs($user)
        ->patchJson('/api/races/'.$race->uid.'/nomination-mark-columns/'.$column->id, [
            'label' => '新ラベル',
        ]);

    // Assert
    $response->assertValidRequest()->assertValidResponse(200);
});

// ===== DELETE /api/races/{uid}/nomination-mark-columns/{id} =====

test('契約: DELETE deleteRaceNominationMarkColumn が OpenAPI 仕様と一致する', function () {
    // Arrange
    $user = User::factory()->create();
    $race = Race::factory()->create();
    $column = RaceNominationMarkColumn::factory()->create([
        'race_id' => $race->id,
        'user_id' => $user->id,
    ]);

    // Act
    $response = $this->actingAs($user)
        ->deleteJson('/api/races/'.$race->uid.'/nomination-mark-columns/'.$column->id);

    // Assert
    $response->assertValidRequest()->assertValidResponse(204);
});
