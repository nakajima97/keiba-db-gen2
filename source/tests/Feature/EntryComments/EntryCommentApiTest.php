<?php

use App\Models\Horse;
use App\Models\Jockey;
use App\Models\JockeyComment;
use App\Models\Race;
use App\Models\RaceEntry;
use App\Models\Trainer;
use App\Models\TrainerComment;
use App\Models\User;
use App\Models\Venue;

/**
 * コメントテスト用のレースと出走馬を 1 件作成する。調教師・騎手は指定がなければ新規作成する。
 *
 * @param  array{race?: Race, trainer?: Trainer|null, jockey?: Jockey, horse_number?: int}  $options
 */
function createEntryForCommentApiTest(array $options = []): RaceEntry
{
    $race = $options['race'] ?? Race::create([
        'venue_id' => Venue::firstOrCreate(['name' => '東京'])->id,
        'race_date' => '2026-11-22',
        'race_number' => fake()->unique()->numberBetween(1, 1000),
    ]);
    $trainer = array_key_exists('trainer', $options) ? $options['trainer'] : Trainer::factory()->create();
    $jockey = $options['jockey'] ?? Jockey::create(['name' => '騎手'.uniqid()]);
    $horse = Horse::create(['name' => '馬'.uniqid(), 'birth_year' => 2022]);

    return RaceEntry::create([
        'race_id' => $race->id,
        'horse_id' => $horse->id,
        'jockey_id' => $jockey->id,
        'trainer_id' => $trainer?->id,
        'frame_number' => 1,
        'horse_number' => $options['horse_number'] ?? 1,
        'weight' => 57.0,
    ]);
}

/**
 * @param  array<string, mixed>  $attributes
 */
function createTrainerCommentForApiTest(User $user, RaceEntry $entry, array $attributes = []): TrainerComment
{
    return TrainerComment::create(array_merge([
        'user_id' => $user->id,
        'race_entry_id' => $entry->id,
        'trainer_id' => $entry->trainer_id,
        'sentiment' => 'positive',
        'content' => '状態は過去最高',
        'outcome' => null,
    ], $attributes));
}

/**
 * @param  array<string, mixed>  $attributes
 */
function createJockeyCommentForApiTest(User $user, RaceEntry $entry, array $attributes = []): JockeyComment
{
    return JockeyComment::create(array_merge([
        'user_id' => $user->id,
        'race_entry_id' => $entry->id,
        'jockey_id' => $entry->jockey_id,
        'sentiment' => 'positive',
        'content' => '手応えは良い',
        'outcome' => null,
    ], $attributes));
}

function entryCommentsStoreUrl(RaceEntry $entry): string
{
    return route('api.races.entries.comments.store', [
        'race' => $entry->race->uid,
        'race_entry_id' => $entry->id,
    ]);
}

function entryCommentUrl(RaceEntry $entry, string $speakerType, int $commentId): string
{
    return route('api.races.entries.comments.update', [
        'race' => $entry->race->uid,
        'race_entry_id' => $entry->id,
        'speaker_type' => $speakerType,
        'comment_id' => $commentId,
    ]);
}

// ===== POST /api/races/{uid}/entries/{race_entry_id}/comments =====

test('未認証ユーザーがコメントを追加しようとするとリダイレクトされる', function () {
    // Arrange
    $entry = createEntryForCommentApiTest();

    // Act
    $response = $this->post(entryCommentsStoreUrl($entry), [
        'speaker_type' => 'trainer',
        'sentiment' => 'positive',
        'content' => 'コメント',
    ]);

    // Assert
    $response->assertRedirect();
    $this->assertGuest();
});

test('調教師コメントを追加すると出走情報の調教師を発言者として 201 で作成され的中率が返る', function () {
    // Arrange
    $user = User::factory()->create();
    $trainer = Trainer::factory()->create(['name' => 'テスト調教師']);
    $entry = createEntryForCommentApiTest(['trainer' => $trainer]);

    // Act
    $response = $this->actingAs($user)->postJson(entryCommentsStoreUrl($entry), [
        'speaker_type' => 'trainer',
        'sentiment' => 'conditional',
        'content' => '展開が向けば',
        'outcome' => 'hit',
    ]);

    // Assert
    $response->assertCreated();
    $response->assertJsonPath('data.speaker_type', 'trainer');
    $response->assertJsonPath('data.speaker_id', $trainer->id);
    $response->assertJsonPath('data.speaker_name', 'テスト調教師');
    $response->assertJsonPath('data.sentiment', 'conditional');
    $response->assertJsonPath('data.content', '展開が向けば');
    $response->assertJsonPath('data.outcome', 'hit');
    $response->assertJsonPath('stats.trainer_id', $trainer->id);
    $response->assertJsonPath('stats.trainer.conditional', ['hit' => 1, 'judged' => 1]);
    $response->assertJsonPath('stats.jockey_id', $entry->jockey_id);
    $this->assertDatabaseHas('trainer_comments', [
        'user_id' => $user->id,
        'race_entry_id' => $entry->id,
        'trainer_id' => $trainer->id,
        'sentiment' => 'conditional',
        'content' => '展開が向けば',
        'outcome' => 'hit',
    ]);
});

test('騎手コメントを当否なしで追加すると出走情報の騎手を発言者として outcome null で作成される', function () {
    // Arrange
    $user = User::factory()->create();
    $entry = createEntryForCommentApiTest();

    // Act
    $response = $this->actingAs($user)->postJson(entryCommentsStoreUrl($entry), [
        'speaker_type' => 'jockey',
        'sentiment' => 'negative',
        'content' => '今回は様子見',
    ]);

    // Assert
    $response->assertCreated();
    $response->assertJsonPath('data.speaker_type', 'jockey');
    $response->assertJsonPath('data.outcome', null);
    $this->assertDatabaseHas('jockey_comments', [
        'user_id' => $user->id,
        'race_entry_id' => $entry->id,
        'jockey_id' => $entry->jockey_id,
        'outcome' => null,
    ]);
});

test('不正な入力でコメントを追加すると 422 が返る', function (array $payload, string $errorKey) {
    // Arrange
    $user = User::factory()->create();
    $entry = createEntryForCommentApiTest();

    // Act
    $response = $this->actingAs($user)->postJson(entryCommentsStoreUrl($entry), array_merge([
        'speaker_type' => 'trainer',
        'sentiment' => 'positive',
        'content' => 'コメント',
    ], $payload));

    // Assert
    $response->assertUnprocessable();
    $response->assertJsonValidationErrors($errorKey);
})->with([
    'content が空' => [['content' => ''], 'content'],
    'content が1000文字超' => [['content' => str_repeat('あ', 1001)], 'content'],
    'sentiment が不正' => [['sentiment' => 'strong'], 'sentiment'],
    'speaker_type が不正' => [['speaker_type' => 'owner'], 'speaker_type'],
    'outcome が不正' => [['outcome' => 'win'], 'outcome'],
]);

test('調教師が未登録の出走馬に調教師コメントを追加すると 422 が返る', function () {
    // Arrange
    $user = User::factory()->create();
    $entry = createEntryForCommentApiTest(['trainer' => null]);

    // Act
    $response = $this->actingAs($user)->postJson(entryCommentsStoreUrl($entry), [
        'speaker_type' => 'trainer',
        'sentiment' => 'positive',
        'content' => 'コメント',
    ]);

    // Assert
    $response->assertUnprocessable();
    $response->assertJsonValidationErrors('speaker_type');
    $this->assertDatabaseCount('trainer_comments', 0);
});

test('指定レースに属さない出走馬にコメントを追加すると 404 が返る', function () {
    // Arrange
    $user = User::factory()->create();
    $entry = createEntryForCommentApiTest();
    $otherEntry = createEntryForCommentApiTest();

    // Act
    $response = $this->actingAs($user)->postJson(route('api.races.entries.comments.store', [
        'race' => $entry->race->uid,
        'race_entry_id' => $otherEntry->id,
    ]), [
        'speaker_type' => 'jockey',
        'sentiment' => 'positive',
        'content' => 'コメント',
    ]);

    // Assert
    $response->assertNotFound();
});

// ===== PATCH /api/races/{uid}/entries/{race_entry_id}/comments/{speaker_type}/{comment_id} =====

test('outcome だけを送ると当否だけが更新され null で未入力に戻る', function () {
    // Arrange
    $user = User::factory()->create();
    $entry = createEntryForCommentApiTest();
    $comment = createTrainerCommentForApiTest($user, $entry, ['sentiment' => 'negative', 'content' => '元の本文']);

    // Act
    $hitResponse = $this->actingAs($user)->patchJson(entryCommentUrl($entry, 'trainer', $comment->id), ['outcome' => 'hit']);
    $resetResponse = $this->actingAs($user)->patchJson(entryCommentUrl($entry, 'trainer', $comment->id), ['outcome' => null]);

    // Assert
    $hitResponse->assertOk();
    $hitResponse->assertJsonPath('data.outcome', 'hit');
    $hitResponse->assertJsonPath('stats.trainer.negative', ['hit' => 1, 'judged' => 1]);
    $resetResponse->assertOk();
    $resetResponse->assertJsonPath('data.outcome', null);
    $resetResponse->assertJsonPath('stats.trainer.negative', ['hit' => 0, 'judged' => 0]);
    $this->assertDatabaseHas('trainer_comments', [
        'id' => $comment->id,
        'sentiment' => 'negative',
        'content' => '元の本文',
        'outcome' => null,
    ]);
});

test('speaker_type を変更すると反対側のテーブルに作り直され created_at は引き継がれる', function () {
    // Arrange
    $user = User::factory()->create();
    $entry = createEntryForCommentApiTest();
    $comment = createTrainerCommentForApiTest($user, $entry, ['content' => '本文', 'outcome' => 'miss']);
    $comment->forceFill(['created_at' => '2026-11-01 10:00:00'])->save();

    // Act
    $response = $this->actingAs($user)->patchJson(entryCommentUrl($entry, 'trainer', $comment->id), [
        'speaker_type' => 'jockey',
        'sentiment' => 'conditional',
    ]);

    // Assert
    $response->assertOk();
    $response->assertJsonPath('data.speaker_type', 'jockey');
    $response->assertJsonPath('data.speaker_id', $entry->jockey_id);
    $response->assertJsonPath('data.sentiment', 'conditional');
    $response->assertJsonPath('data.content', '本文');
    $response->assertJsonPath('data.outcome', 'miss');
    $this->assertDatabaseMissing('trainer_comments', ['id' => $comment->id]);
    $moved = JockeyComment::query()->sole();
    expect($moved->created_at->format('Y-m-d H:i:s'))->toBe('2026-11-01 10:00:00');
    expect($moved->jockey_id)->toBe($entry->jockey_id);
});

test('調教師が未登録の出走馬で speaker_type を trainer に変更すると 422 が返る', function () {
    // Arrange
    $user = User::factory()->create();
    $entry = createEntryForCommentApiTest(['trainer' => null]);
    $comment = createJockeyCommentForApiTest($user, $entry);

    // Act
    $response = $this->actingAs($user)->patchJson(entryCommentUrl($entry, 'jockey', $comment->id), [
        'speaker_type' => 'trainer',
    ]);

    // Assert
    $response->assertUnprocessable();
    $response->assertJsonValidationErrors('speaker_type');
    $this->assertDatabaseHas('jockey_comments', ['id' => $comment->id]);
});

test('他ユーザーのコメントは更新も削除もできず 404 が返る', function () {
    // Arrange
    $owner = User::factory()->create();
    $other = User::factory()->create();
    $entry = createEntryForCommentApiTest();
    $comment = createJockeyCommentForApiTest($owner, $entry);

    // Act
    $patchResponse = $this->actingAs($other)->patchJson(entryCommentUrl($entry, 'jockey', $comment->id), ['outcome' => 'hit']);
    $deleteResponse = $this->actingAs($other)->deleteJson(entryCommentUrl($entry, 'jockey', $comment->id));

    // Assert
    $patchResponse->assertNotFound();
    $deleteResponse->assertNotFound();
    $this->assertDatabaseHas('jockey_comments', ['id' => $comment->id, 'outcome' => null]);
});

// ===== DELETE /api/races/{uid}/entries/{race_entry_id}/comments/{speaker_type}/{comment_id} =====

test('コメントを削除すると物理削除され 200 で集計し直した的中率が返る', function () {
    // Arrange
    $user = User::factory()->create();
    $entry = createEntryForCommentApiTest();
    $comment = createJockeyCommentForApiTest($user, $entry, ['outcome' => 'hit']);

    // Act
    $response = $this->actingAs($user)->deleteJson(entryCommentUrl($entry, 'jockey', $comment->id));

    // Assert
    $response->assertOk();
    $response->assertJsonPath('stats.jockey.positive', ['hit' => 0, 'judged' => 0]);
    $this->assertDatabaseMissing('jockey_comments', ['id' => $comment->id]);
});

// ===== 的中率の集計 =====

test('的中率は自分のコメントを全レース分集計し判定不能・未入力・他ユーザーのコメントを含めない', function () {
    // Arrange
    $user = User::factory()->create();
    $other = User::factory()->create();
    $jockey = Jockey::create(['name' => '集計騎手']);
    $pastEntry = createEntryForCommentApiTest(['jockey' => $jockey]);
    $entry = createEntryForCommentApiTest(['jockey' => $jockey]);
    createJockeyCommentForApiTest($user, $pastEntry, ['sentiment' => 'positive', 'outcome' => 'hit']);
    createJockeyCommentForApiTest($user, $pastEntry, ['sentiment' => 'positive', 'outcome' => 'miss']);
    createJockeyCommentForApiTest($user, $pastEntry, ['sentiment' => 'positive', 'outcome' => 'undeterminable']);
    createJockeyCommentForApiTest($user, $pastEntry, ['sentiment' => 'negative', 'outcome' => null]);
    createJockeyCommentForApiTest($other, $pastEntry, ['sentiment' => 'positive', 'outcome' => 'hit']);

    // Act
    $response = $this->actingAs($user)->postJson(entryCommentsStoreUrl($entry), [
        'speaker_type' => 'jockey',
        'sentiment' => 'positive',
        'content' => '今回も好感触',
        'outcome' => 'hit',
    ]);

    // Assert
    $response->assertCreated();
    $response->assertJsonPath('stats.jockey', [
        'positive' => ['hit' => 2, 'judged' => 3],
        'conditional' => ['hit' => 0, 'judged' => 0],
        'negative' => ['hit' => 0, 'judged' => 0],
    ]);
});
