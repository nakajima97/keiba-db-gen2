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

test('parses horse weight column into expected horse_weight and horse_weight_change', function (string $horseWeightInput, ?int $expectedWeight, ?int $expectedChange) use ($buildResultText) {
    // Arrange
    $parser = new RaceResultHorseParser;
    $text = $buildResultText($horseWeightInput);

    // Act
    $result = $parser->parse($text);

    // Assert
    expect($result[0]['horse_weight'])->toBe($expectedWeight);
    expect($result[0]['horse_weight_change'])->toBe($expectedChange);
})->with([
    'weight only without parentheses' => ['508', 508, null],
    'weight with positive change' => ['508(+4)', 508, 4],
    'weight with negative change' => ['508(-2)', 508, -2],
    'weight with zero change' => ['508(0)', 508, 0],
    'weight with first-run marker' => ['508(初出走)', 508, null],
    'empty horse weight column' => ['', null, null],
]);

// ===== RaceResultHorseParser::parse() 馬体重パース 異常系 =====

test('throws when horse weight column is malformed', function () use ($buildResultText) {
    // Arrange
    $parser = new RaceResultHorseParser;
    $text = $buildResultText('abc');

    // Act & Assert
    expect(fn () => $parser->parse($text))->toThrow(InvalidArgumentException::class);
});
