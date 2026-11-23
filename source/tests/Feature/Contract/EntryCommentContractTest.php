<?php

use App\Models\Horse;
use App\Models\Jockey;
use App\Models\JockeyComment;
use App\Models\Race;
use App\Models\RaceEntry;
use App\Models\Trainer;
use App\Models\User;
use App\Models\Venue;
use Spectator\Spectator;

beforeEach(function () {
    Spectator::using('openapi.yaml');
});

function createEntryForCommentContractTest(): RaceEntry
{
    $race = Race::create([
        'venue_id' => Venue::firstOrCreate(['name' => '東京'])->id,
        'race_date' => '2026-11-22',
        'race_number' => 1,
    ]);

    return RaceEntry::create([
        'race_id' => $race->id,
        'horse_id' => Horse::create(['name' => '契約テスト馬', 'birth_year' => 2022])->id,
        'jockey_id' => Jockey::create(['name' => '契約テスト騎手'])->id,
        'trainer_id' => Trainer::factory()->create()->id,
        'frame_number' => 1,
        'horse_number' => 1,
        'weight' => 57.0,
    ]);
}

function createJockeyCommentForContractTest(User $user, RaceEntry $entry): JockeyComment
{
    return JockeyComment::create([
        'user_id' => $user->id,
        'race_entry_id' => $entry->id,
        'jockey_id' => $entry->jockey_id,
        'sentiment' => 'positive',
        'content' => '手応えは良い',
        'outcome' => null,
    ]);
}

test('契約: createEntryComment が OpenAPI 仕様と一致する', function () {
    // Arrange
    $user = User::factory()->create();
    $entry = createEntryForCommentContractTest();

    // Act
    $response = $this->actingAs($user)->postJson("/api/races/{$entry->race->uid}/entries/{$entry->id}/comments", [
        'speaker_type' => 'trainer',
        'sentiment' => 'conditional',
        'content' => '展開が向けば',
        'outcome' => 'hit',
    ]);

    // Assert
    $response->assertValidRequest()->assertValidResponse(201);
});

test('契約: updateEntryComment が OpenAPI 仕様と一致する', function () {
    // Arrange
    $user = User::factory()->create();
    $entry = createEntryForCommentContractTest();
    $comment = createJockeyCommentForContractTest($user, $entry);

    // Act
    $response = $this->actingAs($user)->patchJson(
        "/api/races/{$entry->race->uid}/entries/{$entry->id}/comments/jockey/{$comment->id}",
        ['outcome' => 'miss'],
    );

    // Assert
    $response->assertValidRequest()->assertValidResponse(200);
});

test('契約: deleteEntryComment が OpenAPI 仕様と一致する', function () {
    // Arrange
    $user = User::factory()->create();
    $entry = createEntryForCommentContractTest();
    $comment = createJockeyCommentForContractTest($user, $entry);

    // Act
    $response = $this->actingAs($user)->deleteJson(
        "/api/races/{$entry->race->uid}/entries/{$entry->id}/comments/jockey/{$comment->id}",
    );

    // Assert
    $response->assertValidRequest()->assertValidResponse(200);
});
