<?php

use App\Models\HorseNote;
use App\Models\RaceNomination;
use App\Models\RaceNominationColumnMark;
use App\Models\RaceNominationColumnMemo;
use App\Models\RaceNominationMark;
use App\Models\RaceNominationMarkColumn;
use App\Models\User;

// ===== DELETE /api/races/{uid}/nominations/{nomination_id} =====

test('登録馬を削除するとチェック／消しも削除され競走馬メモは残る', function () {
    // Arrange
    $user = User::factory()->create();
    $nomination = RaceNomination::factory()->create();
    $mark = RaceNominationMark::factory()->create([
        'race_nomination_id' => $nomination->id,
        'user_id' => $user->id,
    ]);
    $note = HorseNote::factory()->create([
        'user_id' => $user->id,
        'horse_id' => $nomination->horse_id,
        'race_id' => $nomination->race_id,
    ]);

    // Act
    $response = $this->actingAs($user)->deleteJson(
        '/api/races/'.$nomination->race->uid.'/nominations/'.$nomination->id,
    );

    // Assert
    $response->assertNoContent();
    $this->assertDatabaseMissing('race_nominations', ['id' => $nomination->id]);
    $this->assertDatabaseMissing('race_nomination_marks', ['id' => $mark->id]);
    $this->assertDatabaseHas('horse_notes', ['id' => $note->id]);
});

test('登録馬を削除すると他人の印・印メモも削除される', function () {
    // Arrange
    $user = User::factory()->create();
    $nomination = RaceNomination::factory()->create();
    $column = RaceNominationMarkColumn::factory()->create([
        'race_id' => $nomination->race_id,
        'user_id' => $user->id,
    ]);
    $columnMark = RaceNominationColumnMark::factory()->create([
        'race_nomination_mark_column_id' => $column->id,
        'race_nomination_id' => $nomination->id,
    ]);
    $columnMemo = RaceNominationColumnMemo::factory()->create([
        'race_nomination_mark_column_id' => $column->id,
        'race_nomination_id' => $nomination->id,
    ]);

    // Act
    $response = $this->actingAs($user)->deleteJson(
        '/api/races/'.$nomination->race->uid.'/nominations/'.$nomination->id,
    );

    // Assert
    $response->assertNoContent();
    $this->assertDatabaseMissing('race_nomination_column_marks', ['id' => $columnMark->id]);
    $this->assertDatabaseMissing('race_nomination_column_memos', ['id' => $columnMemo->id]);
    $this->assertDatabaseHas('race_nomination_mark_columns', ['id' => $column->id]);
});
