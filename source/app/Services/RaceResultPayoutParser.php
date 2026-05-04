<?php

namespace App\Services;

use App\Enums\TicketTypeName;

/**
 * JRA払い戻しテキストをパースし、券種ごとの払い戻し明細エントリを抽出する。
 *
 * テキストの各行はタブ区切りで「券種名\t馬番\t金額\t人気」の4列。
 * 券種名が空の行は直前の券種の続き（複勝・ワイドなど複数行の券種）。
 * 各行が独立したエントリとなる。
 *
 * @throws \InvalidArgumentException パースに失敗した場合
 */
class RaceResultPayoutParser
{
    /** 券種ラベル → ticket_types.name の対応（tanpuku は本アプリ独自で JRA テキストには現れない） */
    private const TICKET_TYPE_MAP = [
        '単勝' => TicketTypeName::Tansho->value,
        '複勝' => TicketTypeName::Fukusho->value,
        '枠連' => TicketTypeName::Wakuren->value,
        'ワイド' => TicketTypeName::Wide->value,
        '馬連' => TicketTypeName::Umaren->value,
        '馬単' => TicketTypeName::Umatan->value,
        '3連複' => TicketTypeName::Sanrenpuku->value,
        '3連単' => TicketTypeName::Sanrentan->value,
    ];

    /**
     * 必須券種（枠連は任意のため含めない）。
     * 枠連は8頭以下で同一枠に2頭以上いない場合に非発売となるため、
     * JRA券種のうち頭数起因で非発売となり得る唯一の券種である。
     */
    private const REQUIRED_TYPES = [
        TicketTypeName::Tansho->value,
        TicketTypeName::Fukusho->value,
        TicketTypeName::Wide->value,
        TicketTypeName::Umaren->value,
        TicketTypeName::Umatan->value,
        TicketTypeName::Sanrenpuku->value,
        TicketTypeName::Sanrentan->value,
    ];

    /**
     * JRA払い戻しテキストをパースし、各行を独立したエントリとして返す。
     * 複勝・ワイドなど複数行の券種は、各行がそれぞれ独立した払い戻し明細となる。
     *
     * @return array<int, array{ticket_type: string, horse_numbers: array<int, int>, amount: int, popularity: int}>
     *
     * @throws \InvalidArgumentException
     */
    public function parse(string $text): array
    {
        $trimmed = trim($text);
        if ($trimmed === '') {
            throw new \InvalidArgumentException('テキストが空です。');
        }

        $lines = preg_split('/\r?\n/', $trimmed);
        if ($lines === false || $lines === []) {
            throw new \InvalidArgumentException('テキストが空です。');
        }

        /** @var array<int, array{ticket_type: string, horse_numbers: array<int, int>, amount: int, popularity: int}> $entries */
        $entries = [];
        $currentTicketType = null;

        foreach ($lines as $lineNumber => $line) {
            $line = rtrim($line);
            if ($line === '') {
                continue;
            }

            $columns = explode("\t", $line);
            $ticketLabel = trim($columns[0]);

            // JRAコピペフォーマット: 券種名のみの行（例: "単勝"）
            if (count($columns) === 1) {
                if (! isset(self::TICKET_TYPE_MAP[$ticketLabel])) {
                    throw new \InvalidArgumentException(
                        sprintf('%d行目: データの形式が認識できません。', $lineNumber + 1)
                    );
                }
                $currentTicketType = self::TICKET_TYPE_MAP[$ticketLabel];

                continue;
            }

            // JRAコピペフォーマット: データ行（例: "3\t610円\t2番人気"）
            if (count($columns) === 3 && ! isset(self::TICKET_TYPE_MAP[$ticketLabel])) {
                $horseCol = $columns[0];
                $amountCol = $columns[1];
                $popularityCol = $columns[2];
            }
            // インライン / 継続フォーマット（例: "単勝\t3\t610円\t2番人気" or "\t6\t110円\t1番人気"）
            elseif (count($columns) >= 4) {
                if ($ticketLabel !== '') {
                    if (! isset(self::TICKET_TYPE_MAP[$ticketLabel])) {
                        throw new \InvalidArgumentException(
                            sprintf('%d行目: 不明な券種「%s」です。', $lineNumber + 1, $ticketLabel)
                        );
                    }
                    $currentTicketType = self::TICKET_TYPE_MAP[$ticketLabel];
                }
                $horseCol = $columns[1] ?? '';
                $amountCol = $columns[2] ?? '';
                $popularityCol = $columns[3] ?? '';
            } else {
                throw new \InvalidArgumentException(
                    sprintf('%d行目: データの形式が認識できません。', $lineNumber + 1)
                );
            }

            if ($currentTicketType === null) {
                throw new \InvalidArgumentException(
                    sprintf('%d行目: 券種名が特定できません。', $lineNumber + 1)
                );
            }

            $entries[] = [
                'ticket_type' => $currentTicketType,
                'horse_numbers' => $this->parseHorseNumbers($horseCol, $lineNumber + 1),
                'amount' => $this->parseAmount($amountCol, $lineNumber + 1),
                'popularity' => $this->parsePopularity($popularityCol, $lineNumber + 1),
            ];
        }

        return $entries;
    }

    /**
     * パース結果に必須券種（枠連を除く7券種）が揃っていることを検証する。
     *
     * @param  array<int, array{ticket_type: string, horse_numbers: array<int, int>, amount: int, popularity: int}>  $entries
     *
     * @throws \InvalidArgumentException
     */
    public function validateAllTypesPresent(array $entries): void
    {
        $foundTypes = array_unique(array_column($entries, 'ticket_type'));
        $missing = array_diff(self::REQUIRED_TYPES, $foundTypes);

        if ($missing !== []) {
            $missingLabels = [];
            $labelMap = array_flip(self::TICKET_TYPE_MAP);
            foreach ($missing as $name) {
                $missingLabels[] = $labelMap[$name] ?? $name;
            }

            throw new \InvalidArgumentException(
                sprintf('以下の券種が不足しています: %s', implode('、', $missingLabels))
            );
        }
    }

    /**
     * @return array<int, int>
     *
     * @throws \InvalidArgumentException
     */
    private function parseHorseNumbers(string $col, int $lineNumber): array
    {
        $col = trim($col);
        if ($col === '') {
            throw new \InvalidArgumentException(
                sprintf('%d行目: 馬番が空です。', $lineNumber)
            );
        }

        $parts = explode('-', $col);
        $numbers = [];
        foreach ($parts as $part) {
            $part = trim($part);
            if (! ctype_digit($part)) {
                throw new \InvalidArgumentException(
                    sprintf('%d行目: 馬番「%s」が不正です。', $lineNumber, $col)
                );
            }
            $numbers[] = (int) $part;
        }

        return $numbers;
    }

    /**
     * @throws \InvalidArgumentException
     */
    private function parseAmount(string $col, int $lineNumber): int
    {
        $col = trim($col);
        $cleaned = str_replace([',', '円'], '', $col);
        if (! ctype_digit($cleaned)) {
            throw new \InvalidArgumentException(
                sprintf('%d行目: 金額「%s」が不正です。', $lineNumber, $col)
            );
        }

        return (int) $cleaned;
    }

    /**
     * @throws \InvalidArgumentException
     */
    private function parsePopularity(string $col, int $lineNumber): int
    {
        $col = trim($col);
        $cleaned = str_replace('番人気', '', $col);
        if (! ctype_digit($cleaned)) {
            throw new \InvalidArgumentException(
                sprintf('%d行目: 人気「%s」が不正です。', $lineNumber, $col)
            );
        }

        return (int) $cleaned;
    }
}
