<?php

use App\Models\Horse;
use App\Models\HorseNote;
use App\Models\Jockey;
use App\Models\Race;
use App\Models\RaceEntry;
use App\Models\RaceMarkColumn;
use App\Models\RaceNomination;
use App\Models\RaceNominationColumnMark;
use App\Models\RaceNominationColumnMemo;
use App\Models\RaceNominationMark;
use App\Models\RacePayout;
use App\Models\RaceResultHorse;
use App\Models\Trainer;
use App\Models\TrainerNote;
use App\Models\User;
use App\Models\Venue;
use App\Support\NanoId;
use Illuminate\Support\Facades\DB;
use Inertia\Testing\AssertableInertia as Assert;

// ===== GET /races/{uid} =====

test('未認証ユーザーがレース詳細にアクセスするとリダイレクトされる', function () {
    // Arrange
    $venue = Venue::firstOrCreate(['name' => '東京']);
    $race = Race::create([
        'venue_id' => $venue->id,
        'race_date' => '2026-04-05',
        'race_number' => 1,
    ]);

    // Act
    $response = $this->get(route('races.show', ['race' => $race->uid]));

    // Assert
    $response->assertRedirectToRoute('login');
});

test('認証済みユーザーはレース詳細にアクセスでき Inertia コンポーネントが描画される', function () {
    // Arrange
    $user = User::factory()->create();
    $venue = Venue::firstOrCreate(['name' => '東京']);
    $race = Race::create([
        'venue_id' => $venue->id,
        'race_date' => '2026-04-05',
        'race_number' => 1,
    ]);

    // Act
    $response = $this->actingAs($user)->get(route('races.show', ['race' => $race->uid]));

    // Assert
    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page->component('races/show'));
});

test('レース詳細は Inertia props としてレースの基本情報を返す', function () {
    // Arrange
    $user = User::factory()->create();
    $venue = Venue::firstOrCreate(['name' => '東京']);
    $race = Race::create([
        'venue_id' => $venue->id,
        'race_date' => '2026-04-05',
        'race_number' => 3,
        'race_name' => '皐月賞',
    ]);

    // Act
    $response = $this->actingAs($user)->get(route('races.show', ['race' => $race->uid]));

    // Assert
    $response->assertInertia(fn (Assert $page) => $page
        ->component('races/show')
        ->has('race', fn (Assert $race) => $race
            ->where('race_date', '2026-04-05')
            ->where('venue_name', '東京')
            ->where('race_number', 3)
            ->where('race_name', '皐月賞')
            ->etc()
        )
    );
});

test('レース名が null のレースは Inertia props で race_name が null として返される', function () {
    // Arrange
    $user = User::factory()->create();
    $venue = Venue::firstOrCreate(['name' => '東京']);
    $race = Race::create([
        'venue_id' => $venue->id,
        'race_date' => '2026-04-05',
        'race_number' => 1,
    ]);

    // Act
    $response = $this->actingAs($user)->get(route('races.show', ['race' => $race->uid]));

    // Assert
    $response->assertInertia(fn (Assert $page) => $page
        ->component('races/show')
        ->has('race', fn (Assert $race) => $race
            ->where('race_name', null)
            ->etc()
        )
    );
});

test('レース詳細は Inertia props として馬・騎手情報付きの出馬登録を返す', function () {
    // Arrange
    $user = User::factory()->create();
    $venue = Venue::firstOrCreate(['name' => '東京']);
    $race = Race::create([
        'venue_id' => $venue->id,
        'race_date' => '2026-04-05',
        'race_number' => 1,
    ]);

    $now = now();
    $horseId = DB::table('horses')->insertGetId([
        'uid' => NanoId::generate(),
        'name' => 'テストホース',
        'birth_year' => 2022,
        'created_at' => $now,
        'updated_at' => $now,
    ]);
    $jockeyId = DB::table('jockeys')->insertGetId([
        'name' => 'テスト騎手',
        'created_at' => $now,
        'updated_at' => $now,
    ]);
    DB::table('race_entries')->insert([
        'uid' => NanoId::generate(),
        'race_id' => $race->id,
        'horse_id' => $horseId,
        'jockey_id' => $jockeyId,
        'frame_number' => 1,
        'horse_number' => 1,
        'weight' => 55.0,
        'horse_weight' => 480,
        'created_at' => $now,
        'updated_at' => $now,
    ]);

    // Act
    $response = $this->actingAs($user)->get(route('races.show', ['race' => $race->uid]));

    // Assert
    $response->assertInertia(fn (Assert $page) => $page
        ->component('races/show')
        ->has('race.entries', 1, fn (Assert $entry) => $entry
            ->where('frame_number', 1)
            ->where('horse_number', 1)
            ->where('horse_name', 'テストホース')
            ->where('jockey_name', 'テスト騎手')
            ->where('trainer_name', null)
            ->where('trainer_affiliation', null)
            ->where('weight', 480)
            ->etc()
        )
    );
});

test('レース詳細は Inertia props として出馬登録に調教師名と所属を返す', function () {
    // Arrange
    $user = User::factory()->create();
    $venue = Venue::firstOrCreate(['name' => '東京']);
    $race = Race::create([
        'venue_id' => $venue->id,
        'race_date' => '2026-04-05',
        'race_number' => 1,
    ]);
    $trainer = Trainer::factory()->create(['name' => 'テスト調教師', 'affiliation' => '栗東']);
    $horse = Horse::create(['name' => 'テストホース', 'birth_year' => 2022]);
    $jockey = Jockey::create(['name' => 'テスト騎手']);
    RaceEntry::create([
        'race_id' => $race->id,
        'horse_id' => $horse->id,
        'jockey_id' => $jockey->id,
        'trainer_id' => $trainer->id,
        'frame_number' => 1,
        'horse_number' => 1,
        'weight' => 55.0,
    ]);

    // Act
    $response = $this->actingAs($user)->get(route('races.show', ['race' => $race->uid]));

    // Assert
    $response->assertInertia(fn (Assert $page) => $page
        ->has('race.entries', 1, fn (Assert $entry) => $entry
            ->where('trainer_id', $trainer->id)
            ->where('trainer_name', 'テスト調教師')
            ->where('trainer_affiliation', '栗東')
            ->where('trainer_note', null)
            ->etc()
        )
    );
});

test('レース詳細は Inertia props として出馬登録に認証ユーザーの調教師メモのみを返す', function () {
    // Arrange
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $venue = Venue::firstOrCreate(['name' => '東京']);
    $race = Race::create([
        'venue_id' => $venue->id,
        'race_date' => '2026-04-05',
        'race_number' => 1,
    ]);
    $trainer = Trainer::factory()->create();
    $note = TrainerNote::factory()->create([
        'user_id' => $user->id,
        'trainer_id' => $trainer->id,
        'content' => '自分の調教師メモ',
    ]);
    TrainerNote::factory()->create([
        'user_id' => $otherUser->id,
        'trainer_id' => $trainer->id,
        'content' => '他人の調教師メモ',
    ]);
    $jockey = Jockey::create(['name' => 'テスト騎手']);
    foreach ([1, 2] as $horseNumber) {
        RaceEntry::create([
            'race_id' => $race->id,
            'horse_id' => Horse::create(['name' => 'テストホース'.$horseNumber, 'birth_year' => 2022])->id,
            'jockey_id' => $jockey->id,
            'trainer_id' => $trainer->id,
            'frame_number' => 1,
            'horse_number' => $horseNumber,
            'weight' => 55.0,
        ]);
    }

    // Act
    $response = $this->actingAs($user)->get(route('races.show', ['race' => $race->uid]));

    // Assert: 同じ調教師の管理馬すべてに自分のメモが付与される
    $response->assertInertia(fn (Assert $page) => $page
        ->where('race.entries.0.trainer_note', ['id' => $note->id, 'content' => '自分の調教師メモ'])
        ->where('race.entries.1.trainer_note', ['id' => $note->id, 'content' => '自分の調教師メモ'])
    );
});

test('存在しないUIDでレース詳細にアクセスすると404が返る', function () {
    // Arrange
    $user = User::factory()->create();

    // Act
    $response = $this->actingAs($user)->get(route('races.show', ['race' => 'non-existent-uid']));

    // Assert
    $response->assertNotFound();
});

test('レース詳細は Inertia props として自分カテゴリのカラムを含む mark_columns を返す', function () {
    // Arrange
    $user = User::factory()->create();
    $venue = Venue::firstOrCreate(['name' => '東京']);
    $race = Race::create([
        'venue_id' => $venue->id,
        'race_date' => '2026-04-05',
        'race_number' => 1,
    ]);

    // Act
    $response = $this->actingAs($user)->get(route('races.show', ['race' => $race->uid]));

    // Assert
    $response->assertInertia(fn (Assert $page) => $page
        ->component('races/show')
        ->has('race.mark_columns')
        ->where('race.mark_columns', function ($columns) {
            return collect($columns)->pluck('type')->contains('own');
        })
    );
});

test('レース詳細は Inertia props として marks を返す', function () {
    // Arrange
    $user = User::factory()->create();
    $venue = Venue::firstOrCreate(['name' => '東京']);
    $race = Race::create([
        'venue_id' => $venue->id,
        'race_date' => '2026-04-05',
        'race_number' => 1,
    ]);

    // Act
    $response = $this->actingAs($user)->get(route('races.show', ['race' => $race->uid]));

    // Assert
    $response->assertInertia(fn (Assert $page) => $page
        ->component('races/show')
        ->has('race.marks')
        ->where('race.marks', fn ($marks) => is_iterable($marks))
    );
});

test('race_result_horses も race_payouts も存在しない場合、has_result が false として返される', function () {
    // Arrange
    $user = User::factory()->create();
    $race = Race::factory()->create();

    // Act
    $response = $this->actingAs($user)->get(route('races.show', ['race' => $race->uid]));

    // Assert
    $response->assertInertia(fn (Assert $page) => $page
        ->component('races/show')
        ->has('race', fn (Assert $race) => $race
            ->where('has_result', false)
            ->etc()
        )
    );
});

test('race_result_horses が存在する場合、has_result が true として返される', function () {
    // Arrange
    $user = User::factory()->create();
    $race = Race::factory()->create();
    RaceResultHorse::factory()->for($race)->create();

    // Act
    $response = $this->actingAs($user)->get(route('races.show', ['race' => $race->uid]));

    // Assert
    $response->assertInertia(fn (Assert $page) => $page
        ->component('races/show')
        ->has('race', fn (Assert $race) => $race
            ->where('has_result', true)
            ->etc()
        )
    );
});

test('race_payouts が存在する場合、has_result が true として返される', function () {
    // Arrange
    $user = User::factory()->create();
    $race = Race::factory()->create();
    RacePayout::factory()->for($race)->create();

    // Act
    $response = $this->actingAs($user)->get(route('races.show', ['race' => $race->uid]));

    // Assert
    $response->assertInertia(fn (Assert $page) => $page
        ->component('races/show')
        ->has('race', fn (Assert $race) => $race
            ->where('has_result', true)
            ->etc()
        )
    );
});

test('レース詳細は Inertia props として登録馬を登録順に自分のチェック／消し・メモ付きで返す', function () {
    // Arrange
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $race = Race::factory()->create();
    $firstNomination = RaceNomination::factory()->for($race)->create();
    $secondNomination = RaceNomination::factory()->for($race)->create();
    RaceNominationMark::factory()->excluded()->create([
        'race_nomination_id' => $firstNomination->id,
        'user_id' => $user->id,
    ]);
    RaceNominationMark::factory()->create([
        'race_nomination_id' => $secondNomination->id,
        'user_id' => $otherUser->id,
    ]);
    $note = HorseNote::factory()->create([
        'user_id' => $user->id,
        'horse_id' => $firstNomination->horse_id,
        'race_id' => $race->id,
        'content' => '噂レベル',
    ]);

    // Act
    $response = $this->actingAs($user)->get(route('races.show', ['race' => $race->uid]));

    // Assert
    $response->assertInertia(fn (Assert $page) => $page
        ->component('races/show')
        ->has('race.nominations', 2)
        ->where('race.nominations.0', [
            'id' => $firstNomination->id,
            'horse_id' => $firstNomination->horse_id,
            'horse_uid' => $firstNomination->horse->uid,
            'horse_name' => $firstNomination->horse->name,
            'status' => 'excluded',
            'note' => ['id' => $note->id, 'content' => '噂レベル', 'source' => 'race'],
        ])
        ->where('race.nominations.1.id', $secondNomination->id)
        ->where('race.nominations.1.status', null)
        ->where('race.nominations.1.note', null)
    );
});

test('レース詳細は Inertia props として自分の印列の登録馬の印・印メモだけを返す', function () {
    // Arrange
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $race = Race::factory()->create();
    $nomination = RaceNomination::factory()->for($race)->create();
    $column = RaceMarkColumn::factory()->other()->create([
        'race_id' => $race->id,
        'user_id' => $user->id,
    ]);
    $otherUserColumn = RaceMarkColumn::factory()->other()->create([
        'race_id' => $race->id,
        'user_id' => $otherUser->id,
    ]);
    RaceNominationColumnMark::factory()->create([
        'race_mark_column_id' => $column->id,
        'race_nomination_id' => $nomination->id,
        'mark_value' => '▲',
    ]);
    RaceNominationColumnMark::factory()->create([
        'race_mark_column_id' => $otherUserColumn->id,
        'race_nomination_id' => $nomination->id,
    ]);
    RaceNominationColumnMemo::factory()->create([
        'race_mark_column_id' => $column->id,
        'race_nomination_id' => $nomination->id,
        'content' => '印メモ',
    ]);
    RaceNominationColumnMemo::factory()->create([
        'race_mark_column_id' => $otherUserColumn->id,
        'race_nomination_id' => $nomination->id,
    ]);

    // Act
    $response = $this->actingAs($user)->get(route('races.show', ['race' => $race->uid]));

    // Assert
    $response->assertInertia(fn (Assert $page) => $page
        ->component('races/show')
        ->where('race.nomination_marks', [
            ['column_id' => $column->id, 'race_nomination_id' => $nomination->id, 'mark_value' => '▲'],
        ])
        ->where('race.nomination_mark_memos', [
            ['column_id' => $column->id, 'race_nomination_id' => $nomination->id, 'content' => '印メモ'],
        ])
    );
});
