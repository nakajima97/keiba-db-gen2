<?php

use App\Services\TrainerNameParser;

// ===== TrainerNameParser::parse() =====

test('半角括弧の所属付き調教師名が名前と所属に分割される', function () {
    // Act
    $result = TrainerNameParser::parse('加藤 征弘(美浦)');

    // Assert
    expect($result)->toBe(['name' => '加藤 征弘', 'affiliation' => '美浦']);
});

test('全角括弧の所属付き調教師名が名前と所属に分割される', function () {
    // Act
    $result = TrainerNameParser::parse('田中 義人（栗東）');

    // Assert
    expect($result)->toBe(['name' => '田中 義人', 'affiliation' => '栗東']);
});

test('所属がない調教師名は affiliation が null になる', function () {
    // Act
    $result = TrainerNameParser::parse(' 矢作 芳人 ');

    // Assert
    expect($result)->toBe(['name' => '矢作 芳人', 'affiliation' => null]);
});

test('空文字・空白のみの場合は null を返す', function () {
    // Act & Assert
    expect(TrainerNameParser::parse(''))->toBeNull()
        ->and(TrainerNameParser::parse('   '))->toBeNull();
});

// ===== TrainerNameParser::hasAffiliation() =====

test('括弧で始まる行（人気・母の父）は所属付き調教師名と判定されない', function () {
    // Act & Assert
    expect(TrainerNameParser::hasAffiliation('(11番人気)'))->toBeFalse()
        ->and(TrainerNameParser::hasAffiliation('(母の父：Chief Seattle)'))->toBeFalse()
        ->and(TrainerNameParser::hasAffiliation('加藤 征弘(美浦)'))->toBeTrue();
});
