<?php

use App\Services\RaceEntryParser;
use Carbon\Carbon;

/**
 * 1頭分のサンプルテキスト（JRA出馬表からコピーした形式）
 */
$singleEntryText = implode("\n", [
    '枠1白	1	',
    'エビスディアーナ	',
    '127.8',
    '(11番人気)',
    '426kg(-2)',
    '加藤 晃央',
    '',
    '恵比寿牧場',
    '',
    '加藤 征弘(美浦)',
    '',
    '父：マジェスティックウォリアー',
    '母：エビスオール',
    '(母の父：Chief Seattle)',
    '勝負服の画像',
    '',
    '牝3/黒鹿',
    '',
    '55.0kg',
    '',
    'M.ディー',
]);

/**
 * ブリンカー着用馬を含む2頭分のサンプルテキスト
 */
$multipleEntriesText = implode("\n", [
    '枠1白	1	',
    'エビスディアーナ	',
    '127.8',
    '(11番人気)',
    '426kg(-2)',
    '加藤 晃央',
    '',
    '恵比寿牧場',
    '',
    '加藤 征弘(美浦)',
    '',
    '父：マジェスティックウォリアー',
    '母：エビスオール',
    '(母の父：Chief Seattle)',
    '勝負服の画像',
    '',
    '牝3/黒鹿',
    '',
    '55.0kg',
    '',
    'M.ディー',
    '枠2黒	2',
    'ブリンカー着用',
    'オーシャンステラ',
    '130.5',
    '(3番人気)',
    '510kg(+4)',
    '田中 勇',
    '',
    '海洋牧場',
    '',
    '田中 義人(栗東)',
    '',
    '父：ディープインパクト',
    '母：オーシャンウェーブ',
    '(母の父：Storm Cat)',
    '勝負服の画像',
    '',
    '牡5/栗毛',
    '',
    '57.0kg',
    '',
    '武 豊',
]);

// ===== RaceEntryParser::parse() =====

test('ブリンカー表記なしの馬名が正しく抽出される', function () use ($singleEntryText) {
    // Arrange
    $parser = new RaceEntryParser;
    $raceDate = Carbon::create(2026, 4, 18);

    // Act
    $result = $parser->parse($singleEntryText, $raceDate);

    // Assert
    expect($result[0]['horse_name'])->toBe('エビスディアーナ');
});

test('ブリンカー着用表記が馬名に混入しない', function () use ($multipleEntriesText) {
    // Arrange
    $parser = new RaceEntryParser;
    $raceDate = Carbon::create(2026, 4, 18);

    // Act
    $result = $parser->parse($multipleEntriesText, $raceDate);

    // Assert
    expect($result[1]['horse_name'])->toBe('オーシャンステラ');
});

test('「牝3」のような性齢文字列から年齢が正しく抽出される', function () use ($singleEntryText) {
    // Arrange
    $parser = new RaceEntryParser;
    $raceDate = Carbon::create(2026, 4, 18);

    // Act
    $result = $parser->parse($singleEntryText, $raceDate);

    // Assert: 牝3 → age=3 → birth_year = 2026 - 3 = 2023
    expect($result[0]['birth_year'])->toBe(2023);
});

test('レース年と年齢から生年が逆算される', function () use ($singleEntryText) {
    // Arrange
    $parser = new RaceEntryParser;
    $raceDate = Carbon::create(2026, 4, 18);

    // Act
    $result = $parser->parse($singleEntryText, $raceDate);

    // Assert: 2026年・3歳 → 2023年生まれ
    expect($result[0]['birth_year'])->toBe(2023);
});

test('騎手名が正しく抽出される', function () use ($singleEntryText) {
    // Arrange
    $parser = new RaceEntryParser;
    $raceDate = Carbon::create(2026, 4, 18);

    // Act
    $result = $parser->parse($singleEntryText, $raceDate);

    // Assert
    expect($result[0]['jockey_name'])->toBe('M.ディー');
});

test('枠番と馬番が正しく抽出される', function () use ($singleEntryText) {
    // Arrange
    $parser = new RaceEntryParser;
    $raceDate = Carbon::create(2026, 4, 18);

    // Act
    $result = $parser->parse($singleEntryText, $raceDate);

    // Assert
    expect($result[0]['frame_number'])->toBe(1);
    expect($result[0]['horse_number'])->toBe(1);
});

test('斤量が正しく抽出される', function () use ($singleEntryText) {
    // Arrange
    $parser = new RaceEntryParser;
    $raceDate = Carbon::create(2026, 4, 18);

    // Act
    $result = $parser->parse($singleEntryText, $raceDate);

    // Assert: 55.0kg → 55.0
    expect($result[0]['weight'])->toBe(55.0);
});

test('馬体重が正しく抽出される', function () use ($singleEntryText) {
    // Arrange
    $parser = new RaceEntryParser;
    $raceDate = Carbon::create(2026, 4, 18);

    // Act
    $result = $parser->parse($singleEntryText, $raceDate);

    // Assert: 426kg(-2) → 426
    expect($result[0]['horse_weight'])->toBe(426);
});

test('複数頭分のエントリが正しくパースされる', function () use ($multipleEntriesText) {
    // Arrange
    $parser = new RaceEntryParser;
    $raceDate = Carbon::create(2026, 4, 18);

    // Act
    $result = $parser->parse($multipleEntriesText, $raceDate);

    // Assert
    expect(count($result))->toBe(2);

    expect($result[0]['horse_name'])->toBe('エビスディアーナ');
    expect($result[0]['frame_number'])->toBe(1);
    expect($result[0]['horse_number'])->toBe(1);
    expect($result[0]['jockey_name'])->toBe('M.ディー');
    expect($result[0]['weight'])->toBe(55.0);
    expect($result[0]['horse_weight'])->toBe(426);
    expect($result[0]['birth_year'])->toBe(2023);

    expect($result[1]['horse_name'])->toBe('オーシャンステラ');
    expect($result[1]['frame_number'])->toBe(2);
    expect($result[1]['horse_number'])->toBe(2);
    expect($result[1]['jockey_name'])->toBe('武 豊');
    expect($result[1]['weight'])->toBe(57.0);
    expect($result[1]['horse_weight'])->toBe(510);
    expect($result[1]['birth_year'])->toBe(2021);
});

test('馬名行に JRA 注記 prefix が含まれる場合は除去される', function () {
    // Arrange
    $entryTextWithPrefix = implode("\n", [
        '枠1白	1	',
        'マルガイユウファラオ	',
        '127.8',
        '(11番人気)',
        '426kg(-2)',
        '加藤 晃央',
        '',
        '恵比寿牧場',
        '',
        '加藤 征弘(美浦)',
        '',
        '父：マジェスティックウォリアー',
        '母：エビスオール',
        '(母の父：Chief Seattle)',
        '勝負服の画像',
        '',
        '牝3/黒鹿',
        '',
        '55.0kg',
        '',
        'M.ディー',
    ]);
    $parser = new RaceEntryParser;
    $raceDate = Carbon::create(2026, 4, 18);

    // Act
    $result = $parser->parse($entryTextWithPrefix, $raceDate);

    // Assert
    expect($result[0]['horse_name'])->toBe('ユウファラオ');
});

test('調教師名と所属が分割して抽出される', function () use ($multipleEntriesText) {
    // Arrange
    $parser = new RaceEntryParser;
    $raceDate = Carbon::create(2026, 4, 18);

    // Act
    $result = $parser->parse($multipleEntriesText, $raceDate);

    // Assert
    expect($result[0]['trainer_name'])->toBe('加藤 征弘')
        ->and($result[0]['trainer_affiliation'])->toBe('美浦')
        ->and($result[1]['trainer_name'])->toBe('田中 義人')
        ->and($result[1]['trainer_affiliation'])->toBe('栗東');
});

test('調教師行がない場合は trainer_name・trainer_affiliation が null になり他の項目は抽出される', function () {
    // Arrange
    $parser = new RaceEntryParser;
    $raceDate = Carbon::create(2026, 4, 18);
    $textWithoutTrainer = implode("\n", [
        '枠1白	1	',
        'エビスディアーナ	',
        '426kg(-2)',
        '加藤 晃央',
        '恵比寿牧場',
        '牝3/黒鹿',
        '55.0kg',
        'M.ディー',
    ]);

    // Act
    $result = $parser->parse($textWithoutTrainer, $raceDate);

    // Assert
    expect($result)->toHaveCount(1)
        ->and($result[0]['horse_name'])->toBe('エビスディアーナ')
        ->and($result[0]['trainer_name'])->toBeNull()
        ->and($result[0]['trainer_affiliation'])->toBeNull();
});

test('馬主・生産者名や母名が括弧で終わる場合も調教師行が正しく抽出される', function () {
    // Arrange
    $parser = new RaceEntryParser;
    $raceDate = Carbon::create(2026, 4, 18);
    $text = implode("\n", [
        '枠1白	1	',
        'エビスディアーナ	',
        '426kg(-2)',
        'テストホールディングス(株)',
        '',
        'テスト牧場(有)',
        '',
        '加藤 征弘(美浦)',
        '',
        '父：マジェスティックウォリアー',
        '母：エビスオール(IRE)',
        '(母の父：Chief Seattle)',
        '牝3/黒鹿',
        '55.0kg',
        'M.ディー',
    ]);

    // Act
    $result = $parser->parse($text, $raceDate);

    // Assert
    expect($result[0]['trainer_name'])->toBe('加藤 征弘')
        ->and($result[0]['trainer_affiliation'])->toBe('美浦');
});

test('性齢が「せん」表記（騸馬）の馬も正しくパースされる', function () {
    // Arrange
    $parser = new RaceEntryParser;
    $raceDate = Carbon::create(2026, 11, 29);
    $text = implode("\n", [
        '枠5黄	9	',
        'カクガイカランダガン	',
        '5.6',
        '(3番人気)',
        '(1.0.0.0)',
        '50,378万円',
        'アガ・カーン・スタッズSCEA',
        '',
        'Haras De S.A. Aga Khan Scea',
        '',
        'F.グラファール(本会外)',
        '',
        '父：Gleneagles',
        '母：Calayana',
        '(母の父：Sinndar)',
        '勝負服の画像',
        '',
        'せん5/鹿',
        '',
        '58.0kg',
        '',
        'M.バルザローナ',
    ]);

    // Act
    $result = $parser->parse($text, $raceDate);

    // Assert
    expect($result)->toHaveCount(1)
        ->and($result[0]['horse_name'])->toBe('カランダガン')
        ->and($result[0]['birth_year'])->toBe(2021)
        ->and($result[0]['weight'])->toBe(58.0)
        ->and($result[0]['jockey_name'])->toBe('M.バルザローナ');
});

test('性齢が「セ」表記の馬も正しくパースされる', function () {
    // Arrange
    $parser = new RaceEntryParser;
    $raceDate = Carbon::create(2026, 4, 18);
    $text = implode("\n", [
        '枠1白	1	',
        'エビスディアーナ	',
        '加藤 征弘(美浦)',
        'セ4/鹿毛',
        '55.0kg',
        'M.ディー',
    ]);

    // Act
    $result = $parser->parse($text, $raceDate);

    // Assert
    expect($result)->toHaveCount(1)
        ->and($result[0]['birth_year'])->toBe(2022);
});
