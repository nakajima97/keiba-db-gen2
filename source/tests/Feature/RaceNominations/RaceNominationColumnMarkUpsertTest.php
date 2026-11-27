<?php

use App\Models\RaceMarkColumn;
use App\Models\RaceNomination;
use App\Models\RaceNominationColumnMark;
use App\Models\RaceNominationMarkColumn;
use App\Models\User;

/**
 * 登録馬の他人の印 upsert の URL を返す
 */
function nominationColumnMarkUrl(RaceNomination $nomination, int $columnId): string
{
    return '/api/races/'.$nomination->race->uid.'/nomination-mark-columns/'.$columnId.'/nominations/'.$nomination->id.'/mark';
}

// ===== PUT /api/races/{uid}/nomination-mark-columns/{column_id}/nominations/{nomination_id}/mark =====

test('未認証ユーザーは登録馬に他人の印を設定できない', function () {
    // Arrange
    $nomination = RaceNomination::factory()->create();
    $column = RaceNominationMarkColumn::factory()->create(['race_id' => $nomination->race_id]);

    // Act
    $response = $this->putJson(nominationColumnMarkUrl($nomination, $column->id), ['mark_value' => '◎']);

    // Assert
    $response->assertUnauthorized();
});

test('他人の印列で登録馬に印を設定でき既存の値は上書きされる', function () {
    // Arrange
    $user = User::factory()->create();
    $nomination = RaceNomination::factory()->create();
    $column = RaceNominationMarkColumn::factory()->create([
        'race_id' => $nomination->race_id,
        'user_id' => $user->id,
    ]);
    RaceNominationColumnMark::factory()->create([
        'race_nomination_mark_column_id' => $column->id,
        'race_nomination_id' => $nomination->id,
        'mark_value' => '◎',
    ]);

    // Act
    $response = $this->actingAs($user)->putJson(nominationColumnMarkUrl($nomination, $column->id), ['mark_value' => '☆']);

    // Assert
    $response->assertOk();
    $response->assertExactJson([
        'data' => [
            'column_id' => $column->id,
            'race_nomination_id' => $nomination->id,
            'mark_value' => '☆',
        ],
    ]);
    $this->assertDatabaseCount('race_nomination_column_marks', 1);
    $this->assertDatabaseHas('race_nomination_column_marks', [
        'race_nomination_mark_column_id' => $column->id,
        'race_nomination_id' => $nomination->id,
        'mark_value' => '☆',
    ]);
});

test('mark_value に空文字列を指定すると登録馬の印が解除され204が返る', function () {
    // Arrange
    $user = User::factory()->create();
    $nomination = RaceNomination::factory()->create();
    $column = RaceNominationMarkColumn::factory()->create([
        'race_id' => $nomination->race_id,
        'user_id' => $user->id,
    ]);
    RaceNominationColumnMark::factory()->create([
        'race_nomination_mark_column_id' => $column->id,
        'race_nomination_id' => $nomination->id,
    ]);

    // Act
    $response = $this->actingAs($user)->putJson(nominationColumnMarkUrl($nomination, $column->id), ['mark_value' => '']);

    // Assert
    $response->assertNoContent();
    $this->assertDatabaseCount('race_nomination_column_marks', 0);
});

test('mark_value が6種類の印以外の場合は422が返る', function () {
    // Arrange
    $user = User::factory()->create();
    $nomination = RaceNomination::factory()->create();
    $column = RaceNominationMarkColumn::factory()->create([
        'race_id' => $nomination->race_id,
        'user_id' => $user->id,
    ]);

    // Act
    $response = $this->actingAs($user)->putJson(nominationColumnMarkUrl($nomination, $column->id), ['mark_value' => '×']);

    // Assert
    $response->assertUnprocessable();
    $response->assertJsonValidationErrors(['mark_value']);
    $this->assertDatabaseCount('race_nomination_column_marks', 0);
});

test('出馬表の印列IDで登録馬に印を設定しようとすると404が返る', function () {
    // Arrange
    $user = User::factory()->create();
    $nomination = RaceNomination::factory()->create();
    $column = RaceMarkColumn::factory()->other()->create([
        'race_id' => $nomination->race_id,
        'user_id' => $user->id,
    ]);

    // Act
    $response = $this->actingAs($user)->putJson(nominationColumnMarkUrl($nomination, $column->id), ['mark_value' => '◎']);

    // Assert
    $response->assertNotFound();
    $this->assertDatabaseCount('race_nomination_column_marks', 0);
});

test('他ユーザーの印列で登録馬に印を設定しようとすると403が返る', function () {
    // Arrange
    $user = User::factory()->create();
    $nomination = RaceNomination::factory()->create();
    $column = RaceNominationMarkColumn::factory()->create(['race_id' => $nomination->race_id]);

    // Act
    $response = $this->actingAs($user)->putJson(nominationColumnMarkUrl($nomination, $column->id), ['mark_value' => '◎']);

    // Assert
    $response->assertForbidden();
    $this->assertDatabaseCount('race_nomination_column_marks', 0);
});

test('別レースの登録馬に印を設定しようとすると404が返る', function () {
    // Arrange
    $user = User::factory()->create();
    $nomination = RaceNomination::factory()->create();
    $otherRaceNomination = RaceNomination::factory()->create();
    $column = RaceNominationMarkColumn::factory()->create([
        'race_id' => $nomination->race_id,
        'user_id' => $user->id,
    ]);

    // Act
    $response = $this->actingAs($user)->putJson(
        '/api/races/'.$nomination->race->uid.'/nomination-mark-columns/'.$column->id.'/nominations/'.$otherRaceNomination->id.'/mark',
        ['mark_value' => '◎'],
    );

    // Assert
    $response->assertNotFound();
    $this->assertDatabaseCount('race_nomination_column_marks', 0);
});
