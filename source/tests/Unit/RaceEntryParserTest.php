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

test('horse name is correctly extracted without blinker notation', function () use ($singleEntryText) {
    // Arrange
    $parser = new RaceEntryParser;
    $raceDate = Carbon::create(2026, 4, 18);

    // Act
    $result = $parser->parse($singleEntryText, $raceDate);

    // Assert
    expect($result[0]['horse_name'])->toBe('エビスディアーナ');
});

test('blinker notation does not contaminate horse name', function () use ($multipleEntriesText) {
    // Arrange
    $parser = new RaceEntryParser;
    $raceDate = Carbon::create(2026, 4, 18);

    // Act
    $result = $parser->parse($multipleEntriesText, $raceDate);

    // Assert
    expect($result[1]['horse_name'])->toBe('オーシャンステラ');
});

test('age is correctly extracted from sex-age string like 牝3', function () use ($singleEntryText) {
    // Arrange
    $parser = new RaceEntryParser;
    $raceDate = Carbon::create(2026, 4, 18);

    // Act
    $result = $parser->parse($singleEntryText, $raceDate);

    // Assert: 牝3 → age=3 → birth_year = 2026 - 3 = 2023
    expect($result[0]['birth_year'])->toBe(2023);
});

test('birth year is back-calculated from race year and age', function () use ($singleEntryText) {
    // Arrange
    $parser = new RaceEntryParser;
    $raceDate = Carbon::create(2026, 4, 18);

    // Act
    $result = $parser->parse($singleEntryText, $raceDate);

    // Assert: 2026年・3歳 → 2023年生まれ
    expect($result[0]['birth_year'])->toBe(2023);
});

test('jockey name is correctly extracted', function () use ($singleEntryText) {
    // Arrange
    $parser = new RaceEntryParser;
    $raceDate = Carbon::create(2026, 4, 18);

    // Act
    $result = $parser->parse($singleEntryText, $raceDate);

    // Assert
    expect($result[0]['jockey_name'])->toBe('M.ディー');
});

test('frame number and horse number are correctly extracted', function () use ($singleEntryText) {
    // Arrange
    $parser = new RaceEntryParser;
    $raceDate = Carbon::create(2026, 4, 18);

    // Act
    $result = $parser->parse($singleEntryText, $raceDate);

    // Assert
    expect($result[0]['frame_number'])->toBe(1);
    expect($result[0]['horse_number'])->toBe(1);
});

test('weight is correctly extracted', function () use ($singleEntryText) {
    // Arrange
    $parser = new RaceEntryParser;
    $raceDate = Carbon::create(2026, 4, 18);

    // Act
    $result = $parser->parse($singleEntryText, $raceDate);

    // Assert: 55.0kg → 55.0
    expect($result[0]['weight'])->toBe(55.0);
});

test('horse weight is correctly extracted', function () use ($singleEntryText) {
    // Arrange
    $parser = new RaceEntryParser;
    $raceDate = Carbon::create(2026, 4, 18);

    // Act
    $result = $parser->parse($singleEntryText, $raceDate);

    // Assert: 426kg(-2) → 426
    expect($result[0]['horse_weight'])->toBe(426);
});

test('multiple entries are parsed correctly', function () use ($multipleEntriesText) {
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
