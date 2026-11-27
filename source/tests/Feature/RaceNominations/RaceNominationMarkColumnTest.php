<?php

use App\Models\Race;
use App\Models\RaceMarkColumn;
use App\Models\RaceNomination;
use App\Models\RaceNominationColumnMark;
use App\Models\RaceNominationColumnMemo;
use App\Models\RaceNominationMarkColumn;
use App\Models\User;

/**
 * 登録馬用の印列 API の URL を返す
 */
function nominationMarkColumnUrl(Race $race, ?int $columnId = null): string
{
    $url = '/api/races/'.$race->uid.'/nomination-mark-columns';

    return $columnId === null ? $url : $url.'/'.$columnId;
}

// ===== GET /api/races/{uid}/nomination-mark-columns =====

test('自分の登録馬用印列だけが display_order 昇順で返り出馬表の印列は含まれない', function () {
    // Arrange
    $user = User::factory()->create();
    $race = Race::factory()->create();
    RaceMarkColumn::factory()->other()->create([
        'race_id' => $race->id,
        'user_id' => $user->id,
        'display_order' => 1,
    ]);
    $second = RaceNominationMarkColumn::factory()->create([
        'race_id' => $race->id,
        'user_id' => $user->id,
        'label' => '友人B',
        'display_order' => 2,
    ]);
    $first = RaceNominationMarkColumn::factory()->create([
        'race_id' => $race->id,
        'user_id' => $user->id,
        'label' => '友人A',
        'display_order' => 1,
    ]);
    RaceNominationMarkColumn::factory()->create(['race_id' => $race->id]);

    // Act
    $response = $this->actingAs($user)->getJson(nominationMarkColumnUrl($race));

    // Assert
    $response->assertOk();
    $response->assertExactJson([
        'data' => [
            ['id' => $first->id, 'label' => '友人A', 'display_order' => 1],
            ['id' => $second->id, 'label' => '友人B', 'display_order' => 2],
        ],
    ]);
});

// ===== POST /api/races/{uid}/nomination-mark-columns =====

test('登録馬用印列を末尾に追加でき出馬表の印列は増えない', function () {
    // Arrange
    $user = User::factory()->create();
    $race = Race::factory()->create();
    RaceNominationMarkColumn::factory()->create([
        'race_id' => $race->id,
        'user_id' => $user->id,
        'display_order' => 3,
    ]);

    // Act
    $response = $this->actingAs($user)->postJson(nominationMarkColumnUrl($race), ['label' => '友人A']);

    // Assert
    $response->assertCreated();
    $response->assertJsonPath('data.label', '友人A');
    $response->assertJsonPath('data.display_order', 4);
    $this->assertDatabaseCount('race_nomination_mark_columns', 2);
    $this->assertDatabaseCount('race_mark_columns', 0);
});

test('出馬表の印列を追加しても登録馬用印列は増えない', function () {
    // Arrange
    $user = User::factory()->create();
    $race = Race::factory()->create();

    // Act
    $response = $this->actingAs($user)->postJson('/api/races/'.$race->uid.'/mark-columns', ['label' => '友人A']);

    // Assert
    $response->assertCreated();
    $this->assertDatabaseCount('race_nomination_mark_columns', 0);
});

// ===== PATCH /api/races/{uid}/nomination-mark-columns/{id} =====

test('登録馬用印列のラベルを更新できる', function () {
    // Arrange
    $user = User::factory()->create();
    $race = Race::factory()->create();
    $column = RaceNominationMarkColumn::factory()->create([
        'race_id' => $race->id,
        'user_id' => $user->id,
        'label' => '旧ラベル',
    ]);

    // Act
    $response = $this->actingAs($user)->patchJson(nominationMarkColumnUrl($race, $column->id), ['label' => '新ラベル']);

    // Assert
    $response->assertOk();
    $response->assertJsonPath('data.label', '新ラベル');
    $this->assertDatabaseHas('race_nomination_mark_columns', ['id' => $column->id, 'label' => '新ラベル']);
});

test('他ユーザーの登録馬用印列のラベルを更新しようとすると403が返る', function () {
    // Arrange
    $user = User::factory()->create();
    $race = Race::factory()->create();
    $column = RaceNominationMarkColumn::factory()->create(['race_id' => $race->id, 'label' => '旧ラベル']);

    // Act
    $response = $this->actingAs($user)->patchJson(nominationMarkColumnUrl($race, $column->id), ['label' => '新ラベル']);

    // Assert
    $response->assertForbidden();
    $this->assertDatabaseHas('race_nomination_mark_columns', ['id' => $column->id, 'label' => '旧ラベル']);
});

// ===== DELETE /api/races/{uid}/nomination-mark-columns/{id} =====

test('登録馬用印列を削除するとその列の印・印メモも削除される', function () {
    // Arrange
    $user = User::factory()->create();
    $race = Race::factory()->create();
    $nomination = RaceNomination::factory()->for($race)->create();
    $column = RaceNominationMarkColumn::factory()->create([
        'race_id' => $race->id,
        'user_id' => $user->id,
    ]);
    RaceNominationColumnMark::factory()->create([
        'race_nomination_mark_column_id' => $column->id,
        'race_nomination_id' => $nomination->id,
    ]);
    RaceNominationColumnMemo::factory()->create([
        'race_nomination_mark_column_id' => $column->id,
        'race_nomination_id' => $nomination->id,
    ]);

    // Act
    $response = $this->actingAs($user)->deleteJson(nominationMarkColumnUrl($race, $column->id));

    // Assert
    $response->assertNoContent();
    $this->assertDatabaseCount('race_nomination_mark_columns', 0);
    $this->assertDatabaseCount('race_nomination_column_marks', 0);
    $this->assertDatabaseCount('race_nomination_column_memos', 0);
});

test('他ユーザーの登録馬用印列を削除しようとすると403が返る', function () {
    // Arrange
    $user = User::factory()->create();
    $race = Race::factory()->create();
    $column = RaceNominationMarkColumn::factory()->create(['race_id' => $race->id]);

    // Act
    $response = $this->actingAs($user)->deleteJson(nominationMarkColumnUrl($race, $column->id));

    // Assert
    $response->assertForbidden();
    $this->assertDatabaseHas('race_nomination_mark_columns', ['id' => $column->id]);
});

test('出馬表の印列IDで登録馬用印列を削除しようとすると404が返り出馬表の印列は残る', function () {
    // Arrange
    $user = User::factory()->create();
    $race = Race::factory()->create();
    $entryColumn = RaceMarkColumn::factory()->other()->create([
        'race_id' => $race->id,
        'user_id' => $user->id,
    ]);

    // Act
    $response = $this->actingAs($user)->deleteJson(nominationMarkColumnUrl($race, $entryColumn->id));

    // Assert
    $response->assertNotFound();
    $this->assertDatabaseHas('race_mark_columns', ['id' => $entryColumn->id]);
});
