<?php

use App\Enums\TicketTypeName;
use App\Services\RaceResultPayoutParser;

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

test('parses JRA copy-paste format with ticket-type-only line followed by data line', function () {
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

test('parses inline format with ticket type, horse number, amount, popularity in one line', function () {
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

test('parses continuation format where leading column is empty', function () {
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

test('multi-row ticket types (fukusho, wide) produce independent entries per line', function () {
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

test('amount with comma is parsed correctly', function () {
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

test('multiple horse numbers separated by hyphen are parsed into array', function () {
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

test('three-horse ticket parses three horse numbers', function () {
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

test('parses complete payout text with seven required ticket types (no wakuren)', function () use ($completePayoutTextWithoutWakuren) {
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

test('parses complete payout text with all eight ticket types (with wakuren)', function () use ($completePayoutTextWithWakuren) {
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

test('blank lines are ignored', function () {
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

test('throws when text is empty', function () {
    // Arrange
    $parser = new RaceResultPayoutParser;

    // Act & Assert
    expect(fn () => $parser->parse(''))
        ->toThrow(\InvalidArgumentException::class, 'テキストが空です。');
});

test('throws when ticket-type-only line is unknown', function () {
    // Arrange
    $parser = new RaceResultPayoutParser;
    $text = implode("\n", [
        '謎券種',
        "3\t610円\t2番人気",
    ]);

    // Act & Assert
    expect(fn () => $parser->parse($text))->toThrow(\InvalidArgumentException::class);
});

test('throws when inline format ticket type is unknown', function () {
    // Arrange
    $parser = new RaceResultPayoutParser;
    $text = "謎券種\t3\t610円\t2番人気";

    // Act & Assert
    expect(fn () => $parser->parse($text))->toThrow(\InvalidArgumentException::class);
});

test('throws when line has invalid column count', function () {
    // Arrange
    $parser = new RaceResultPayoutParser;
    $text = implode("\n", [
        '単勝',
        "3\t610円",
    ]);

    // Act & Assert
    expect(fn () => $parser->parse($text))->toThrow(\InvalidArgumentException::class);
});

test('throws when data line appears before any ticket type is established', function () {
    // Arrange
    $parser = new RaceResultPayoutParser;
    $text = "3\t610円\t2番人気";

    // Act & Assert
    expect(fn () => $parser->parse($text))->toThrow(\InvalidArgumentException::class);
});

test('throws when data column is invalid', function (string $dataLine) {
    // Arrange
    $parser = new RaceResultPayoutParser;
    $text = "単勝\n".$dataLine;

    // Act & Assert
    expect(fn () => $parser->parse($text))->toThrow(\InvalidArgumentException::class);
})->with([
    'empty horse number' => "\t610円\t2番人気",
    'non-numeric horse number' => "A-B\t610円\t2番人気",
    'invalid amount' => "3\tabc円\t2番人気",
    'invalid popularity' => "3\t610円\tabc番人気",
]);

// ===== RaceResultPayoutParser::validateAllTypesPresent() =====

test('does not throw when all seven required ticket types are present', function () {
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

test('throws when entries is empty', function () {
    // Arrange
    $parser = new RaceResultPayoutParser;

    // Act & Assert
    expect(fn () => $parser->validateAllTypesPresent([]))->toThrow(\InvalidArgumentException::class);
});

test('throws when only wakuren is present and required types are missing', function () {
    // Arrange
    $parser = new RaceResultPayoutParser;
    $entries = [
        ['ticket_type' => TicketTypeName::Wakuren->value, 'horse_numbers' => [2, 4], 'amount' => 900, 'popularity' => 3],
    ];

    // Act & Assert
    expect(fn () => $parser->validateAllTypesPresent($entries))->toThrow(\InvalidArgumentException::class);
});

test('throws and includes missing ticket label in Japanese when one type is missing', function () {
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
        ->toThrow(\InvalidArgumentException::class, '単勝');
});

test('throws when multiple required types are missing', function () {
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
    expect(fn () => $parser->validateAllTypesPresent($entries))->toThrow(\InvalidArgumentException::class);
});
