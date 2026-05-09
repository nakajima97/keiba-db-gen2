<?php

use App\Enums\TicketTypeName;
use App\Services\RaceResultPayoutParser;
use InvalidArgumentException;

/**
 * 7券種すべてを含む完全なJRAコピペ形式の払戻テキスト（枠連なし）
 */
$completePayoutTextWithoutWakuren = implode("\n", [
    '単勝',
    "3\t610円\t2番人気",
    '複勝',
    "3\t110円\t1番人気",
    "6\t220円\t3番人気",
    "9\t150円\t2番人気",
    'ワイド',
    "3-6\t450円\t2番人気",
    "3-9\t380円\t1番人気",
    "6-9\t520円\t3番人気",
    '馬連',
    "3-6\t1,200円\t4番人気",
    '馬単',
    "3-6\t2,400円\t7番人気",
    '3連複',
    "3-6-9\t1,800円\t5番人気",
    '3連単',
    "3-6-9\t8,500円\t12番人気",
]);

/**
 * 8券種すべてを含む完全なJRAコピペ形式の払戻テキスト（枠連あり）
 */
$completePayoutTextWithWakuren = implode("\n", [
    '単勝',
    "3\t610円\t2番人気",
    '複勝',
    "3\t110円\t1番人気",
    "6\t220円\t3番人気",
    "9\t150円\t2番人気",
    '枠連',
    "2-4\t900円\t3番人気",
    'ワイド',
    "3-6\t450円\t2番人気",
    "3-9\t380円\t1番人気",
    "6-9\t520円\t3番人気",
    '馬連',
    "3-6\t1,200円\t4番人気",
    '馬単',
    "3-6\t2,400円\t7番人気",
    '3連複',
    "3-6-9\t1,800円\t5番人気",
    '3連単',
    "3-6-9\t8,500円\t12番人気",
]);

// ===== RaceResultPayoutParser::parse() 正常系 =====

test('券種行とデータ行が分かれた JRA コピペ形式をパースできる', function () {
    // Arrange
    $parser = new RaceResultPayoutParser;
    $text = implode("\n", [
        '単勝',
        "3\t610円\t2番人気",
    ]);

    // Act
    $result = $parser->parse($text);

    // Assert
    expect(count($result))->toBe(1);
    expect($result[0]['ticket_type'])->toBe(TicketTypeName::Tansho->value);
    expect($result[0]['horse_numbers'])->toBe([3]);
    expect($result[0]['amount'])->toBe(610);
    expect($result[0]['popularity'])->toBe(2);
});

test('券種・馬番・金額・人気が1行に並んだインライン形式をパースできる', function () {
    // Arrange
    $parser = new RaceResultPayoutParser;
    $text = "単勝\t3\t610円\t2番人気";

    // Act
    $result = $parser->parse($text);

    // Assert
    expect(count($result))->toBe(1);
    expect($result[0]['ticket_type'])->toBe(TicketTypeName::Tansho->value);
    expect($result[0]['horse_numbers'])->toBe([3]);
    expect($result[0]['amount'])->toBe(610);
    expect($result[0]['popularity'])->toBe(2);
});

test('先頭カラムが空の継続行形式をパースできる', function () {
    // Arrange
    $parser = new RaceResultPayoutParser;
    $text = implode("\n", [
        "複勝\t3\t110円\t1番人気",
        "\t6\t220円\t3番人気",
    ]);

    // Act
    $result = $parser->parse($text);

    // Assert
    expect(count($result))->toBe(2);
    expect($result[0]['ticket_type'])->toBe(TicketTypeName::Fukusho->value);
    expect($result[1]['ticket_type'])->toBe(TicketTypeName::Fukusho->value);
});

test('複勝・ワイドなどの複数行券種は1行ごとに独立したエントリとなる', function () {
    // Arrange
    $parser = new RaceResultPayoutParser;
    $text = implode("\n", [
        '複勝',
        "3\t110円\t1番人気",
        "6\t220円\t3番人気",
        "9\t150円\t2番人気",
    ]);

    // Act
    $result = $parser->parse($text);

    // Assert
    expect(count($result))->toBe(3);
    expect($result[0]['ticket_type'])->toBe(TicketTypeName::Fukusho->value);
    expect($result[1]['ticket_type'])->toBe(TicketTypeName::Fukusho->value);
    expect($result[2]['ticket_type'])->toBe(TicketTypeName::Fukusho->value);
});

test('カンマ区切りの金額が正しくパースされる', function () {
    // Arrange
    $parser = new RaceResultPayoutParser;
    $text = implode("\n", [
        '3連単',
        "1-2-3\t12,380円\t5番人気",
    ]);

    // Act
    $result = $parser->parse($text);

    // Assert
    expect($result[0]['amount'])->toBe(12380);
});

test('ハイフン区切りの複数馬番が配列にパースされる', function () {
    // Arrange
    $parser = new RaceResultPayoutParser;
    $text = implode("\n", [
        '馬連',
        "3-6\t1,200円\t4番人気",
    ]);

    // Act
    $result = $parser->parse($text);

    // Assert
    expect($result[0]['horse_numbers'])->toBe([3, 6]);
});

test('3頭組の券種が3つの馬番にパースされる', function () {
    // Arrange
    $parser = new RaceResultPayoutParser;
    $text = implode("\n", [
        '3連複',
        "1-2-3\t820円\t1番人気",
    ]);

    // Act
    $result = $parser->parse($text);

    // Assert
    expect($result[0]['horse_numbers'])->toBe([1, 2, 3]);
});

test('必須7券種を含む完全な払戻テキスト（枠連なし）をパースできる', function () use ($completePayoutTextWithoutWakuren) {
    // Arrange
    $parser = new RaceResultPayoutParser;

    // Act
    $result = $parser->parse($completePayoutTextWithoutWakuren);

    // Assert
    $ticketTypes = array_map(fn ($entry) => $entry['ticket_type'], $result);
    expect($ticketTypes)->toContain(TicketTypeName::Tansho->value);
    expect($ticketTypes)->toContain(TicketTypeName::Fukusho->value);
    expect($ticketTypes)->toContain(TicketTypeName::Wide->value);
    expect($ticketTypes)->toContain(TicketTypeName::Umaren->value);
    expect($ticketTypes)->toContain(TicketTypeName::Umatan->value);
    expect($ticketTypes)->toContain(TicketTypeName::Sanrenpuku->value);
    expect($ticketTypes)->toContain(TicketTypeName::Sanrentan->value);
});

test('全8券種を含む完全な払戻テキスト（枠連あり）をパースできる', function () use ($completePayoutTextWithWakuren) {
    // Arrange
    $parser = new RaceResultPayoutParser;

    // Act
    $result = $parser->parse($completePayoutTextWithWakuren);

    // Assert
    $ticketTypes = array_map(fn ($entry) => $entry['ticket_type'], $result);
    expect($ticketTypes)->toContain(TicketTypeName::Tansho->value);
    expect($ticketTypes)->toContain(TicketTypeName::Fukusho->value);
    expect($ticketTypes)->toContain(TicketTypeName::Wakuren->value);
    expect($ticketTypes)->toContain(TicketTypeName::Wide->value);
    expect($ticketTypes)->toContain(TicketTypeName::Umaren->value);
    expect($ticketTypes)->toContain(TicketTypeName::Umatan->value);
    expect($ticketTypes)->toContain(TicketTypeName::Sanrenpuku->value);
    expect($ticketTypes)->toContain(TicketTypeName::Sanrentan->value);
});

test('空行は無視される', function () {
    // Arrange
    $parser = new RaceResultPayoutParser;
    $text = implode("\n", [
        '単勝',
        '',
        "3\t610円\t2番人気",
        '',
        '複勝',
        "3\t110円\t1番人気",
    ]);

    // Act
    $result = $parser->parse($text);

    // Assert
    expect(count($result))->toBe(2);
    expect($result[0]['ticket_type'])->toBe(TicketTypeName::Tansho->value);
    expect($result[1]['ticket_type'])->toBe(TicketTypeName::Fukusho->value);
});

// ===== RaceResultPayoutParser::parse() 異常系 =====

test('テキストが空のとき例外が投げられる', function () {
    // Arrange
    $parser = new RaceResultPayoutParser;

    // Act & Assert
    expect(fn () => $parser->parse(''))
        ->toThrow(InvalidArgumentException::class, 'テキストが空です。');
});

test('券種行が未知の券種名のとき例外が投げられる', function () {
    // Arrange
    $parser = new RaceResultPayoutParser;
    $text = implode("\n", [
        '謎券種',
        "3\t610円\t2番人気",
    ]);

    // Act & Assert
    expect(fn () => $parser->parse($text))->toThrow(InvalidArgumentException::class);
});

test('インライン形式の券種名が未知のとき例外が投げられる', function () {
    // Arrange
    $parser = new RaceResultPayoutParser;
    $text = "謎券種\t3\t610円\t2番人気";

    // Act & Assert
    expect(fn () => $parser->parse($text))->toThrow(InvalidArgumentException::class);
});

test('行のカラム数が不正のとき例外が投げられる', function () {
    // Arrange
    $parser = new RaceResultPayoutParser;
    $text = implode("\n", [
        '単勝',
        "3\t610円",
    ]);

    // Act & Assert
    expect(fn () => $parser->parse($text))->toThrow(InvalidArgumentException::class);
});

test('券種が確定する前にデータ行が出現したとき例外が投げられる', function () {
    // Arrange
    $parser = new RaceResultPayoutParser;
    $text = "3\t610円\t2番人気";

    // Act & Assert
    expect(fn () => $parser->parse($text))->toThrow(InvalidArgumentException::class);
});

test('データ行のカラム値が不正のとき例外が投げられる', function (string $dataLine) {
    // Arrange
    $parser = new RaceResultPayoutParser;
    $text = "単勝\n".$dataLine;

    // Act & Assert
    expect(fn () => $parser->parse($text))->toThrow(InvalidArgumentException::class);
})->with([
    '馬番が空' => "\t610円\t2番人気",
    '馬番が数値でない' => "A-B\t610円\t2番人気",
    '金額が不正' => "3\tabc円\t2番人気",
    '人気が不正' => "3\t610円\tabc番人気",
]);

// ===== RaceResultPayoutParser::validateAllTypesPresent() =====

test('必須7券種すべてが揃っているとき例外が投げられない', function () {
    // Arrange
    $parser = new RaceResultPayoutParser;
    $entries = [
        ['ticket_type' => TicketTypeName::Tansho->value, 'horse_numbers' => [3], 'amount' => 610, 'popularity' => 2],
        ['ticket_type' => TicketTypeName::Fukusho->value, 'horse_numbers' => [3], 'amount' => 110, 'popularity' => 1],
        ['ticket_type' => TicketTypeName::Wide->value, 'horse_numbers' => [3, 6], 'amount' => 450, 'popularity' => 2],
        ['ticket_type' => TicketTypeName::Umaren->value, 'horse_numbers' => [3, 6], 'amount' => 1200, 'popularity' => 4],
        ['ticket_type' => TicketTypeName::Umatan->value, 'horse_numbers' => [3, 6], 'amount' => 2400, 'popularity' => 7],
        ['ticket_type' => TicketTypeName::Sanrenpuku->value, 'horse_numbers' => [3, 6, 9], 'amount' => 1800, 'popularity' => 5],
        ['ticket_type' => TicketTypeName::Sanrentan->value, 'horse_numbers' => [3, 6, 9], 'amount' => 8500, 'popularity' => 12],
    ];

    // Act & Assert
    $parser->validateAllTypesPresent($entries);
    expect(true)->toBe(true);
});

test('エントリが空のとき例外が投げられる', function () {
    // Arrange
    $parser = new RaceResultPayoutParser;

    // Act & Assert
    expect(fn () => $parser->validateAllTypesPresent([]))->toThrow(InvalidArgumentException::class);
});

test('枠連のみで必須券種が欠けているとき例外が投げられる', function () {
    // Arrange
    $parser = new RaceResultPayoutParser;
    $entries = [
        ['ticket_type' => TicketTypeName::Wakuren->value, 'horse_numbers' => [2, 4], 'amount' => 900, 'popularity' => 3],
    ];

    // Act & Assert
    expect(fn () => $parser->validateAllTypesPresent($entries))->toThrow(InvalidArgumentException::class);
});

test('1券種が欠けているとき例外メッセージに欠けた券種の日本語ラベルが含まれる', function () {
    // Arrange
    $parser = new RaceResultPayoutParser;
    $entries = [
        ['ticket_type' => TicketTypeName::Fukusho->value, 'horse_numbers' => [3], 'amount' => 110, 'popularity' => 1],
        ['ticket_type' => TicketTypeName::Wide->value, 'horse_numbers' => [3, 6], 'amount' => 450, 'popularity' => 2],
        ['ticket_type' => TicketTypeName::Umaren->value, 'horse_numbers' => [3, 6], 'amount' => 1200, 'popularity' => 4],
        ['ticket_type' => TicketTypeName::Umatan->value, 'horse_numbers' => [3, 6], 'amount' => 2400, 'popularity' => 7],
        ['ticket_type' => TicketTypeName::Sanrenpuku->value, 'horse_numbers' => [3, 6, 9], 'amount' => 1800, 'popularity' => 5],
        ['ticket_type' => TicketTypeName::Sanrentan->value, 'horse_numbers' => [3, 6, 9], 'amount' => 8500, 'popularity' => 12],
    ];

    // Act & Assert
    expect(fn () => $parser->validateAllTypesPresent($entries))
        ->toThrow(InvalidArgumentException::class, '単勝');
});

test('複数の必須券種が欠けているとき例外が投げられる', function () {
    // Arrange
    $parser = new RaceResultPayoutParser;
    $entries = [
        ['ticket_type' => TicketTypeName::Wide->value, 'horse_numbers' => [3, 6], 'amount' => 450, 'popularity' => 2],
        ['ticket_type' => TicketTypeName::Umaren->value, 'horse_numbers' => [3, 6], 'amount' => 1200, 'popularity' => 4],
        ['ticket_type' => TicketTypeName::Umatan->value, 'horse_numbers' => [3, 6], 'amount' => 2400, 'popularity' => 7],
        ['ticket_type' => TicketTypeName::Sanrenpuku->value, 'horse_numbers' => [3, 6, 9], 'amount' => 1800, 'popularity' => 5],
        ['ticket_type' => TicketTypeName::Sanrentan->value, 'horse_numbers' => [3, 6, 9], 'amount' => 8500, 'popularity' => 12],
    ];

    // Act & Assert
    expect(fn () => $parser->validateAllTypesPresent($entries))->toThrow(InvalidArgumentException::class);
});
