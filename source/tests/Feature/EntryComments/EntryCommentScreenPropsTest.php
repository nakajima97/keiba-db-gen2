<?php

use App\Models\Horse;
use App\Models\Jockey;
use App\Models\JockeyComment;
use App\Models\Race;
use App\Models\RaceEntry;
use App\Models\RaceResultHorse;
use App\Models\Trainer;
use App\Models\TrainerComment;
use App\Models\User;
use App\Models\Venue;
use Inertia\Testing\AssertableInertia as Assert;

function createRaceForCommentPropsTest(int $raceNumber): Race
{
    return Race::create([
        'venue_id' => Venue::firstOrCreate(['name' => '東京'])->id,
        'race_date' => '2026-11-22',
        'race_number' => $raceNumber,
    ]);
}

function createEntryForCommentPropsTest(Race $race, int $horseNumber, Jockey $jockey, ?Trainer $trainer): RaceEntry
{
    $horse = Horse::create(['name' => '馬'.uniqid(), 'birth_year' => 2022]);

    return RaceEntry::create([
        'race_id' => $race->id,
        'horse_id' => $horse->id,
        'jockey_id' => $jockey->id,
        'trainer_id' => $trainer?->id,
        'frame_number' => 1,
        'horse_number' => $horseNumber,
        'weight' => 57.0,
    ]);
}

function createResultHorseForCommentPropsTest(Race $race, int $finishingOrder, int $horseNumber, ?int $horseId = null): void
{
    RaceResultHorse::create([
        'race_id' => $race->id,
        'horse_id' => $horseId,
        'finishing_order' => $finishingOrder,
        'frame_number' => 1,
        'horse_number' => $horseNumber,
        'horse_name' => '結果馬'.$horseNumber,
        'sex_age' => '牡3',
        'weight' => 57.0,
        'jockey_name' => '騎手',
        'race_time' => '1:34.5',
        'trainer_name' => '調教師',
        'popularity' => 1,
    ]);
}

test('レース詳細の出馬表に自分のコメント（古い順）と騎手ID・調教師/騎手ごとの全レース分の的中率が含まれる', function () {
    // Arrange
    $user = User::factory()->create();
    $other = User::factory()->create();
    $trainer = Trainer::factory()->create(['name' => 'テスト調教師']);
    $jockey = Jockey::create(['name' => 'テスト騎手']);
    $pastRace = createRaceForCommentPropsTest(1);
    $race = createRaceForCommentPropsTest(2);
    $pastEntry = createEntryForCommentPropsTest($pastRace, 1, $jockey, $trainer);
    $entry = createEntryForCommentPropsTest($race, 1, $jockey, $trainer);
    TrainerComment::create([
        'user_id' => $user->id, 'race_entry_id' => $pastEntry->id, 'trainer_id' => $trainer->id,
        'sentiment' => 'positive', 'content' => '過去', 'outcome' => 'miss',
    ]);
    JockeyComment::create([
        'user_id' => $user->id, 'race_entry_id' => $entry->id, 'jockey_id' => $jockey->id,
        'sentiment' => 'conditional', 'content' => '騎手の後のコメント', 'outcome' => 'hit',
        'created_at' => '2026-11-21 12:00:00',
    ]);
    TrainerComment::create([
        'user_id' => $user->id, 'race_entry_id' => $entry->id, 'trainer_id' => $trainer->id,
        'sentiment' => 'positive', 'content' => '調教師の先のコメント', 'outcome' => null,
        'created_at' => '2026-11-20 12:00:00',
    ]);
    JockeyComment::create([
        'user_id' => $other->id, 'race_entry_id' => $entry->id, 'jockey_id' => $jockey->id,
        'sentiment' => 'conditional', 'content' => '他人のコメント', 'outcome' => 'hit',
    ]);

    // Act
    $response = $this->actingAs($user)->get(route('races.show', ['race' => $race->uid]));

    // Assert
    $response->assertInertia(fn (Assert $page) => $page
        ->has('race.entries', 1, fn (Assert $props) => $props
            ->where('jockey_id', $jockey->id)
            ->has('comments', 2)
            ->where('comments.0.speaker_type', 'trainer')
            ->where('comments.0.speaker_name', 'テスト調教師')
            ->where('comments.0.content', '調教師の先のコメント')
            ->where('comments.1.speaker_type', 'jockey')
            ->where('comments.1.content', '騎手の後のコメント')
            ->where('trainer_comment_stats.positive', ['hit' => 0, 'judged' => 1])
            ->where('jockey_comment_stats.conditional', ['hit' => 1, 'judged' => 1])
            ->etc()
        )
    );
});

test('調教師が未登録の出走馬は trainer_comment_stats が null でコメントがなければ comments は空配列になる', function () {
    // Arrange
    $user = User::factory()->create();
    $race = createRaceForCommentPropsTest(1);
    createEntryForCommentPropsTest($race, 1, Jockey::create(['name' => 'テスト騎手']), null);

    // Act
    $response = $this->actingAs($user)->get(route('races.show', ['race' => $race->uid]));

    // Assert
    $response->assertInertia(fn (Assert $page) => $page
        ->has('race.entries', 1, fn (Assert $props) => $props
            ->where('comments', [])
            ->where('trainer_comment_stats', null)
            ->where('jockey_comment_stats', [
                'positive' => ['hit' => 0, 'judged' => 0],
                'conditional' => ['hit' => 0, 'judged' => 0],
                'negative' => ['hit' => 0, 'judged' => 0],
            ])
            ->etc()
        )
    );
});

test('レース結果編集画面の着順には馬番で対応付けた出走馬のコメントが含まれ対応する出走馬がないか別の馬なら null になる', function () {
    // Arrange
    $user = User::factory()->create();
    $jockey = Jockey::create(['name' => 'テスト騎手']);
    $race = createRaceForCommentPropsTest(1);
    $entry = createEntryForCommentPropsTest($race, 5, $jockey, null);
    $entryNumber3 = createEntryForCommentPropsTest($race, 3, $jockey, null);
    createEntryForCommentPropsTest($race, 7, $jockey, null);
    $otherHorse = Horse::create(['name' => '別の馬', 'birth_year' => 2022]);
    JockeyComment::create([
        'user_id' => $user->id, 'race_entry_id' => $entry->id, 'jockey_id' => $jockey->id,
        'sentiment' => 'positive', 'content' => '好感触', 'outcome' => null,
    ]);
    createResultHorseForCommentPropsTest($race, 1, 5, $entry->horse_id);
    createResultHorseForCommentPropsTest($race, 2, 3, $entryNumber3->horse_id);
    createResultHorseForCommentPropsTest($race, 3, 9);
    createResultHorseForCommentPropsTest($race, 4, 7, $otherHorse->id);

    // Act
    $response = $this->actingAs($user)->get(route('races.result.edit', ['uid' => $race->uid]));

    // Assert
    $response->assertInertia(fn (Assert $page) => $page
        ->where('race.finishing_horses.0.race_entry_id', $entry->id)
        ->has('race.finishing_horses.0.comments', 1)
        ->where('race.finishing_horses.0.comments.0.content', '好感触')
        ->where('race.finishing_horses.1.comments', [])
        ->where('race.finishing_horses.2.race_entry_id', null)
        ->where('race.finishing_horses.2.comments', null)
        ->where('race.finishing_horses.3.race_entry_id', null)
        ->where('race.finishing_horses.3.comments', null)
    );
});
