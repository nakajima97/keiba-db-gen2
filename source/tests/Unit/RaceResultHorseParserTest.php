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
