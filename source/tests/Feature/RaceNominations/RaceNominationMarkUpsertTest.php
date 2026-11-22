<?php

use App\Models\RaceNomination;
use App\Models\RaceNominationMark;
use App\Models\User;

// ===== PUT /api/races/{uid}/nominations/{nomination_id}/mark =====

test('登録馬にチェック／消しを設定でき既存の値は上書きされる', function () {
    // Arrange
    $user = User::factory()->create();
    $nomination = RaceNomination::factory()->create();
    RaceNominationMark::factory()->create([
        'race_nomination_id' => $nomination->id,
        'user_id' => $user->id,
        'status' => 'check',
    ]);

    // Act
    $response = $this->actingAs($user)->putJson(
        '/api/races/'.$nomination->race->uid.'/nominations/'.$nomination->id.'/mark',
        ['status' => 'excluded'],
    );

    // Assert
    $response->assertOk();
    $response->assertJsonPath('data.race_nomination_id', $nomination->id);
    $response->assertJsonPath('data.status', 'excluded');
    $this->assertDatabaseCount('race_nomination_marks', 1);
    $this->assertDatabaseHas('race_nomination_marks', [
        'race_nomination_id' => $nomination->id,
        'user_id' => $user->id,
        'status' => 'excluded',
    ]);
});

test('status に空文字列を指定すると自分のチェック／消しだけが解除される', function () {
    // Arrange
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $nomination = RaceNomination::factory()->create();
    RaceNominationMark::factory()->create([
        'race_nomination_id' => $nomination->id,
        'user_id' => $user->id,
    ]);
    $otherMark = RaceNominationMark::factory()->excluded()->create([
        'race_nomination_id' => $nomination->id,
        'user_id' => $otherUser->id,
    ]);

    // Act
    $response = $this->actingAs($user)->putJson(
        '/api/races/'.$nomination->race->uid.'/nominations/'.$nomination->id.'/mark',
        ['status' => ''],
    );

    // Assert
    $response->assertNoContent();
    $this->assertDatabaseMissing('race_nomination_marks', [
        'race_nomination_id' => $nomination->id,
        'user_id' => $user->id,
    ]);
    $this->assertDatabaseHas('race_nomination_marks', ['id' => $otherMark->id]);
});
