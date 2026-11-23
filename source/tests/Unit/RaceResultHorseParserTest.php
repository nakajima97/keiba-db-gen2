<?php

use App\Services\RaceResultHorseParser;
use InvalidArgumentException;

/**
 * 3行形式の着順テキストブロックを構築する。
 * `{馬体重}` 部分のみテストごとに差し替えるためのヘルパー。
 */
$buildResultText = function (string $horseWeight): string {
    return "1\t枠1白\t1\tテスト馬\t牡4\t57.0\tテスト騎手\t1:35.0\t\n"
        ."1 1 1 1\n"
        ."35.5\t{$horseWeight}\tテスト調教師\t1";
};

// ===== RaceResultHorseParser::parse() 馬体重パース 正常系 =====

test('馬体重カラムが想定どおり horse_weight と horse_weight_change にパースされる', function (string $horseWeightInput, ?int $expectedWeight, ?int $expectedChange) use ($buildResultText) {
    // Arrange
    $parser = new RaceResultHorseParser;
    $text = $buildResultText($horseWeightInput);

    // Act
    $result = $parser->parse($text);

    // Assert
    expect($result[0]['horse_weight'])->toBe($expectedWeight);
    expect($result[0]['horse_weight_change'])->toBe($expectedChange);
})->with([
    '括弧なしの体重のみ' => ['508', 508, null],
    'プラスの増減を含む体重' => ['508(+4)', 508, 4],
    'マイナスの増減を含む体重' => ['508(-2)', 508, -2],
    '増減ゼロの体重' => ['508(0)', 508, 0],
    '初出走マーク付きの体重' => ['508(初出走)', 508, null],
    '体重カラムが空' => ['', null, null],
]);

// ===== RaceResultHorseParser::parse() 馬体重パース 異常系 =====

test('馬体重カラムが不正な形式のとき例外が投げられる', function () use ($buildResultText) {
    // Arrange
    $parser = new RaceResultHorseParser;
    $text = $buildResultText('abc');

    // Act & Assert
    expect(fn () => $parser->parse($text))->toThrow(InvalidArgumentException::class);
});

// ===== RaceResultHorseParser::parse() 馬名 JRA 注記 prefix の除去 =====

test('馬名カラムに JRA 注記 prefix が含まれる場合は除去される', function () {
    // Arrange
    $parser = new RaceResultHorseParser;
    $text = "1\t枠1白\t1\tマルガイテスト馬\t牡4\t57.0\tテスト騎手\t1:35.0\t\n"
        ."1 1 1 1\n"
        ."35.5\t508\tテスト調教師\t1";

    // Act
    $result = $parser->parse($text);

    // Assert
    expect($result[0]['horse_name'])->toBe('テスト馬');
});

// ===== RaceResultHorseParser::parse() レコード・直線コース・Rt 列 (#312) =====

/**
 * Issue #312 のサンプル（新潟直線1000m、1着がレコード）を JRA 公式サイトから
 * コピーしたときのテキスト（抜粋）。ヘッダー行も含む。
 */
$straightCourseRecordText = implode("\n", [
    "着順\t枠\t馬",
    "番\t馬名\t性齢\t負担",
    "重量\t騎手名\tタイム\t着差\tコーナー",
    '通過順位',
    "推定上り\t馬体重",
    '（増減）',
    "調教師名\t単勝",
    "人気\tRt",
    "1\t枠3赤\t6\tピューロマジック\t牝5\t56.0\t田辺 裕信\t0:53.5\t",
    'レコード',
    "31.7\t460(+2)\t安田 翔伍\t1\t109",
    "2\t枠6緑\t11\tロードトレイル\t牡5\t57.0\t大野 拓弥\t0:54.0\t３\t\t32.0\t478(+4)\t藤岡 健一\t14\t102",
    "4\t枠8桃\t17\t",
    'ビッグシーザーブリンカー着用',
    "牡6\t57.0\t荻野 極\t0:54.2\t１\t\t32.0\t520(0)\t杉山 晴紀\t4\t99",
    "5\t枠8桃\t16\tマルガイアメリカンステージ\t牡4\t57.0\t戸崎 圭太\t0:54.3\t３／４\t\t32.3\t514(+6)\t矢作 芳人\t2\t97",
]);

test('直線コースでレコードを含む着順テキストをパースできる', function () use ($straightCourseRecordText) {
    // Arrange
    $parser = new RaceResultHorseParser;

    // Act
    $result = $parser->parse($straightCourseRecordText);

    // Assert
    expect($result)->toHaveCount(4);
    expect($result[0])->toBe([
        'finishing_order' => 1,
        'frame_number' => 3,
        'horse_number' => 6,
        'horse_name' => 'ピューロマジック',
        'sex_age' => '牝5',
        'weight' => 56.0,
        'jockey_name' => '田辺 裕信',
        'race_time' => '0:53.5',
        'time_difference' => 'レコード',
        'corner_order' => null,
        'estimated_pace' => 31.7,
        'horse_weight' => 460,
        'horse_weight_change' => 2,
        'trainer_name' => '安田 翔伍',
        'popularity' => 1,
    ]);
    expect($result[1])->toMatchArray([
        'finishing_order' => 2,
        'horse_name' => 'ロードトレイル',
        'time_difference' => '３',
        'corner_order' => null,
        'estimated_pace' => 32.0,
        'horse_weight' => 478,
        'horse_weight_change' => 4,
        'trainer_name' => '藤岡 健一',
        'popularity' => 14,
    ]);
    expect($result[2])->toMatchArray([
        'finishing_order' => 4,
        'frame_number' => 8,
        'horse_number' => 17,
        'horse_name' => 'ビッグシーザー',
        'sex_age' => '牡6',
        'jockey_name' => '荻野 極',
        'time_difference' => '１',
        'corner_order' => null,
        'trainer_name' => '杉山 晴紀',
        'popularity' => 4,
    ]);
    expect($result[3])->toMatchArray([
        'horse_name' => 'アメリカンステージ',
        'time_difference' => '３／４',
        'popularity' => 2,
    ]);
});

test('コーナーのあるコースでレコードの馬はコーナー通過順位と着差を正しく分けてパースできる', function () {
    // Arrange
    $parser = new RaceResultHorseParser;
    $text = implode("\n", [
        "1\t枠1白\t1\tテスト馬A\t牡4\t57.0\t騎手A\t1:31.5\t",
        'レコード',
        '2 2 2 1',
        "34.0\t500(+2)\t調教師A\t1\t110",
        "2\t枠2黒\t2\t",
        'テスト馬Bブリンカー着用',
        "牝4\t55.0\t騎手B\t1:31.6\t",
        'レコード',
        '1 1 1 2',
        "34.3\t480(-4)\t調教師B\t3\t105",
        "3\t枠3赤\t3\tテスト馬C\t牡5\t58.0\t騎手C\t1:31.9\t２\t",
        '3 3 3 3',
        "34.5\t490(0)\t調教師C\t2\t100",
    ]);

    // Act
    $result = $parser->parse($text);

    // Assert
    expect($result)->toHaveCount(3);
    expect($result[0])->toMatchArray([
        'horse_name' => 'テスト馬A',
        'time_difference' => 'レコード',
        'corner_order' => '2 2 2 1',
        'estimated_pace' => 34.0,
        'popularity' => 1,
    ]);
    expect($result[1])->toMatchArray([
        'horse_name' => 'テスト馬B',
        'sex_age' => '牝4',
        'race_time' => '1:31.6',
        'time_difference' => 'レコード',
        'corner_order' => '1 1 1 2',
        'trainer_name' => '調教師B',
        'popularity' => 3,
    ]);
    expect($result[2])->toMatchArray([
        'time_difference' => '２',
        'corner_order' => '3 3 3 3',
        'popularity' => 2,
    ]);
});

test('着差が空の3行形式ではコーナー通過順位として扱われる', function () use ($buildResultText) {
    // Arrange
    $parser = new RaceResultHorseParser;

    // Act
    $result = $parser->parse($buildResultText('508(+4)'));

    // Assert
    expect($result[0]['time_difference'])->toBeNull();
    expect($result[0]['corner_order'])->toBe('1 1 1 1');
    expect($result[0]['trainer_name'])->toBe('テスト調教師');
    expect($result[0]['popularity'])->toBe(1);
});

test('列数が足りない馬ブロックは例外が投げられる', function () {
    // Arrange
    $parser = new RaceResultHorseParser;
    $text = "1\t枠1白\t1\tテスト馬\t牡4\t57.0\tテスト騎手\t1:35.0";

    // Act & Assert
    expect(fn () => $parser->parse($text))->toThrow(InvalidArgumentException::class);
});
