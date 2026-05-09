<?php

use App\Models\Horse;
use App\Models\Jockey;
use App\Models\Race;
use App\Models\RaceEntry;
use App\Support\NanoId;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

afterEach(function () {
    Horse::clearUidGeneratorState();
    RaceEntry::clearUidGeneratorState();
    Race::clearUidGeneratorState();
});

test('uid 未指定で create すると NanoId が自動採番される', function () {
    $horse = Horse::create(['name' => 'テスト馬', 'birth_year' => 2020]);

    expect($horse->uid)->toBeString();
    expect(strlen($horse->uid))->toBe(21);
});

test('uid を明示指定して create するとその値が保持される', function () {
    $horse = Horse::create([
        'uid' => 'explicit-uid-12345',
        'name' => 'テスト馬',
        'birth_year' => 2020,
    ]);

    expect($horse->uid)->toBe('explicit-uid-12345');
});

test('uid が UNIQUE 衝突したとき再採番してリトライ成功する', function () {
    $existing = Horse::create([
        'uid' => 'collision-uid',
        'name' => '先発',
        'birth_year' => 2020,
    ]);

    Horse::fakeUidQueue([$existing->uid, 'fresh-unique-uid-001']);

    $horse = Horse::create(['name' => '後発', 'birth_year' => 2021]);

    expect($horse->uid)->toBe('fresh-unique-uid-001');
    expect(Horse::count())->toBe(2);
});

test('uid のリトライは最大 3 回まで実行され超過時は QueryException が throw される', function () {
    Horse::create([
        'uid' => 'always-collide',
        'name' => '先発',
        'birth_year' => 2020,
    ]);

    // 初回採番 + 3 リトライの計 4 回すべて衝突
    Horse::fakeUidQueue(array_fill(0, 4, 'always-collide'));

    expect(fn () => Horse::create(['name' => '後発', 'birth_year' => 2021]))
        ->toThrow(QueryException::class);

    // 後発はコミットされていない
    expect(Horse::count())->toBe(1);
});

test('他カラムの UNIQUE 違反 (race_id, horse_number) はリトライされず即 throw される', function () {
    $horse1 = Horse::create(['name' => '馬1', 'birth_year' => 2020]);
    $horse2 = Horse::create(['name' => '馬2', 'birth_year' => 2020]);
    $jockey = Jockey::create(['name' => '騎手1']);
    $race = Race::factory()->create();

    RaceEntry::create([
        'race_id' => $race->id,
        'horse_id' => $horse1->id,
        'jockey_id' => $jockey->id,
        'frame_number' => 1,
        'horse_number' => 1,
        'weight' => 55.0,
    ]);

    DB::flushQueryLog();
    DB::enableQueryLog();

    expect(fn () => RaceEntry::create([
        'race_id' => $race->id,
        'horse_id' => $horse2->id,
        'jockey_id' => $jockey->id,
        'frame_number' => 2,
        'horse_number' => 1, // (race_id, horse_number) で衝突
        'weight' => 55.0,
    ]))->toThrow(QueryException::class);

    // race_entries への INSERT は 1 回のみ。リトライされていないことを保証する。
    $insertCount = collect(DB::getQueryLog())
        ->filter(fn ($q) => str_contains($q['query'], 'race_entries')
            && stripos($q['query'], 'insert') === 0)
        ->count();
    expect($insertCount)->toBe(1);

    DB::disableQueryLog();
});

test('Race モデルでも UNIQUE 衝突時にリトライされる', function () {
    $existing = Race::factory()->create(['uid' => 'race-collision']);

    Race::fakeUidQueue(['race-collision', 'race-fresh-uid']);

    $race = Race::factory()->create();

    expect($race->uid)->toBe('race-fresh-uid');
});

test('RaceEntry モデルでも UNIQUE 衝突時にリトライされる', function () {
    $horse1 = Horse::create(['name' => '馬1', 'birth_year' => 2020]);
    $horse2 = Horse::create(['name' => '馬2', 'birth_year' => 2020]);
    $jockey = Jockey::create(['name' => '騎手1']);
    $race = Race::factory()->create();

    $existing = RaceEntry::create([
        'uid' => 'entry-collision',
        'race_id' => $race->id,
        'horse_id' => $horse1->id,
        'jockey_id' => $jockey->id,
        'frame_number' => 1,
        'horse_number' => 1,
        'weight' => 55.0,
    ]);

    RaceEntry::fakeUidQueue([$existing->uid, 'entry-fresh-uid']);

    $entry = RaceEntry::create([
        'race_id' => $race->id,
        'horse_id' => $horse2->id,
        'jockey_id' => $jockey->id,
        'frame_number' => 2,
        'horse_number' => 2,
        'weight' => 55.0,
    ]);

    expect($entry->uid)->toBe('entry-fresh-uid');
});

test('DB トランザクション内で uid 衝突→リトライしても正常に保存できる', function () {
    Horse::create([
        'uid' => 'trans-collision',
        'name' => '先発',
        'birth_year' => 2020,
    ]);

    Horse::fakeUidQueue(['trans-collision', 'trans-fresh-uid']);

    $horse = DB::transaction(function () {
        return Horse::create(['name' => '後発', 'birth_year' => 2021]);
    });

    expect($horse->uid)->toBe('trans-fresh-uid');
    expect(Horse::count())->toBe(2);
});

test('既存レコードの update 時は uid を再採番しない', function () {
    $horse = Horse::create([
        'uid' => 'keep-this-uid',
        'name' => '初期名',
        'birth_year' => 2020,
    ]);

    Horse::fakeUidQueue(['should-not-be-used']);

    $horse->name = '変更後の名前';
    $horse->save();

    expect($horse->fresh()->uid)->toBe('keep-this-uid');
    expect($horse->fresh()->name)->toBe('変更後の名前');
});

test('fakeUidQueue はモデル間で独立している', function () {
    Horse::fakeUidQueue(['horse-only-uid']);
    Race::fakeUidQueue(['race-only-uid']);

    $horse = Horse::create(['name' => 'テスト', 'birth_year' => 2020]);
    $race = Race::factory()->create();

    expect($horse->uid)->toBe('horse-only-uid');
    expect($race->uid)->toBe('race-only-uid');
});

test('NanoId::generate がフォールバックとして呼ばれる (fakeUidQueue 空時)', function () {
    Horse::fakeUidQueue([]);

    $horse = Horse::create(['name' => 'テスト', 'birth_year' => 2020]);

    // フォールバック後の NanoId 採番値が 21 文字英数字_-である
    expect($horse->uid)->toMatch('/^[A-Za-z0-9_-]{21}$/');
});

test('NanoId クラスの生成値とトレイトの採番ロジックが整合する', function () {
    // smoke: NanoId::generate() を直接呼んで形式を確認
    $generated = NanoId::generate();
    expect(strlen($generated))->toBe(21);

    $horse = Horse::create(['name' => 'テスト', 'birth_year' => 2020]);
    expect(strlen($horse->uid))->toBe(21);
});
