<?php

use App\Services\HorseNameNormalizer;

// ===== HorseNameNormalizer::stripJraAnnotationPrefix() =====

test('JRA 注記 prefix が馬名先頭に付いている場合は除去される', function (string $input, string $expected) {
    expect(HorseNameNormalizer::stripJraAnnotationPrefix($input))->toBe($expected);
})->with([
    'マルガイ（外国産馬）' => ['マルガイユウファラオ', 'ユウファラオ'],
    'カクガイ（外国馬）' => ['カクガイテストホース', 'テストホース'],
    'マルチ（地方競馬所属馬）' => ['マルチオーシャンステラ', 'オーシャンステラ'],
]);

test('注記 prefix を含まない馬名は変更されない', function () {
    expect(HorseNameNormalizer::stripJraAnnotationPrefix('エビスディアーナ'))
        ->toBe('エビスディアーナ');
});

test('空文字は空文字のまま返される', function () {
    expect(HorseNameNormalizer::stripJraAnnotationPrefix(''))->toBe('');
});

test('prefix 文字列と完全一致する場合は除去せずに元の文字列を返す', function (string $input) {
    expect(HorseNameNormalizer::stripJraAnnotationPrefix($input))->toBe($input);
})->with([
    'マルガイのみ' => ['マルガイ'],
    'カクガイのみ' => ['カクガイ'],
    'マルチのみ' => ['マルチ'],
]);
