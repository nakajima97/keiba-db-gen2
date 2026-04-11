<?php

namespace App\UseCases\RaceResult;

use App\Models\Race;
use App\Models\RacePayout;
use App\Models\RacePayoutHorse;
use App\Models\TicketPurchase;
use Illuminate\Support\Facades\DB;

/**
 * JRA払い戻しテキストをパースし、race_payouts / race_payout_horses に保存する。
 *
 * テキストの各行はタブ区切りで「券種名\t馬番\t金額\t人気」の4列。
 * 券種名が空の行は直前の券種の続き（複勝・ワイドなど複数行の券種）。
 * 各行が独立した race_payouts レコードとなり、
 * 行内の馬番は race_payout_horses に保存する。
 *
 * @throws \InvalidArgumentException パースに失敗した場合
 */
class StoreAction
{
    /** 券種ラベル → ticket_types.name の対応 */
    private const TICKET_TYPE_MAP = [
        '単勝' => 'tansho',
        '複勝' => 'fukusho',
        '枠連' => 'wakuren',
        'ワイド' => 'wide',
        '馬連' => 'umaren',
        '馬単' => 'umatan',
        '3連複' => 'sanrenpuku',
        '3連単' => 'sanrentan',
    ];

    /** 順序を保持する券種（着順どおり保存） */
    private const ORDERED_TYPES = ['umatan', 'sanrentan'];

    /** 全8券種 */
    private const REQUIRED_TYPES = [
        'tansho', 'fukusho', 'wakuren', 'wide',
        'umaren', 'umatan', 'sanrenpuku', 'sanrentan',
    ];

    /** 券種ごとの指定頭数（照合対象の馬番数） */
    private const TICKET_TYPE_HORSE_COUNT = [
        'tansho' => 1,
        'fukusho' => 1,
        'wakuren' => 2,
        'umaren' => 2,
        'umatan' => 2,
        'wide' => 2,
        'sanrenpuku' => 3,
        'sanrentan' => 3,
    ];

    /**
     * @param  array{text: string}  $data
     */
    public function execute(array $data, string $uid, int $userId): void
    {
        $race = Race::where('uid', $uid)->firstOrFail();
        $raceId = $race->id;

        $entries = $this->parse($data['text']);
        $this->validateAllTypesPresent($entries);

        $ticketTypeIds = DB::table('ticket_types')
            ->whereIn('name', self::REQUIRED_TYPES)
            ->pluck('id', 'name')
            ->all();

        DB::transaction(function () use ($entries, $raceId, $ticketTypeIds, $userId): void {
            foreach ($entries as $entry) {
                $ticketTypeId = $ticketTypeIds[$entry['ticket_type']];

                $payout = RacePayout::create([
                    'race_id' => $raceId,
                    'ticket_type_id' => $ticketTypeId,
                    'payout_amount' => $entry['amount'],
                    'popularity' => $entry['popularity'],
                ]);

                $isOrdered = in_array($entry['ticket_type'], self::ORDERED_TYPES, true);
                $horseNumbers = $entry['horse_numbers'];

                if (! $isOrdered) {
                    sort($horseNumbers);
                }

                foreach ($horseNumbers as $index => $horseNumber) {
                    RacePayoutHorse::create([
                        'race_payout_id' => $payout->id,
                        'horse_number' => $horseNumber,
                        'sort_order' => $index + 1,
                    ]);
                }
            }

            $this->updateTicketPurchasesPayoutAmount($raceId, $userId);
        });
    }

    /**
     * 対象レースの購入馬券（投稿ユーザーのみ）を取得し、レース結果と照合して payout_amount を更新する。
     *
     * 各 TicketPurchase の selections を buy_type に応じて組み合わせに展開し、
     * 同じレースの race_payouts（ticket_type ごとの払い戻し行）の馬番配列と照合する。
     * ヒットした組み合わせの payout_amount を合算し、1件以上ヒットした場合のみ更新する。
     */
    private function updateTicketPurchasesPayoutAmount(int $raceId, int $userId): void
    {
        /** @var \Illuminate\Database\Eloquent\Collection<int, RacePayout> $payouts */
        $payouts = RacePayout::with(['ticketType', 'racePayoutHorses' => function ($query): void {
            $query->orderBy('sort_order');
        }])->where('race_id', $raceId)->get();

        /** @var array<string, array<int, array{amount: int, horse_numbers: array<int, int>}>> $payoutsByType */
        $payoutsByType = [];
        foreach ($payouts as $payout) {
            $typeName = $payout->ticketType->name;
            $horseNumbers = $payout->racePayoutHorses
                ->sortBy('sort_order')
                ->pluck('horse_number')
                ->map(fn ($n) => (int) $n)
                ->values()
                ->all();

            $payoutsByType[$typeName] ??= [];
            $payoutsByType[$typeName][] = [
                'amount' => (int) $payout->payout_amount,
                'horse_numbers' => $horseNumbers,
            ];
        }

        /** @var \Illuminate\Database\Eloquent\Collection<int, TicketPurchase> $purchases */
        $purchases = TicketPurchase::with(['ticketType', 'buyType'])
            ->where('race_id', $raceId)
            ->where('user_id', $userId)
            ->get();

        foreach ($purchases as $purchase) {
            $ticketTypeName = $purchase->ticketType->name;
            $buyTypeName = $purchase->buyType->name;
            $selections = $purchase->selections ?? [];

            $horseCount = self::TICKET_TYPE_HORSE_COUNT[$ticketTypeName] ?? null;
            if ($horseCount === null) {
                continue;
            }

            $isOrdered = in_array($ticketTypeName, self::ORDERED_TYPES, true);
            $combinations = $this->expandSelections($buyTypeName, $selections, $horseCount, $isOrdered);

            if ($combinations === []) {
                continue;
            }

            $totalPayout = $this->sumMatchedPayouts(
                $combinations,
                $payoutsByType[$ticketTypeName] ?? [],
                $isOrdered,
            );

            if ($totalPayout > 0) {
                $purchase->payout_amount = $totalPayout;
                $purchase->save();
            }
        }
    }

    /**
     * buy_type と ticket_type の指定頭数に応じて selections を組み合わせに展開する。
     *
     * 着順考慮（umatan / sanrentan）の場合、各組み合わせは順序を保持した配列。
     * 着順不問の場合、各組み合わせは昇順ソート済みの配列。
     *
     * @param  array<string, mixed>  $selections
     * @return array<int, array<int, int>>
     */
    private function expandSelections(string $buyType, array $selections, int $horseCount, bool $isOrdered): array
    {
        $combinations = match ($buyType) {
            'single' => $this->expandSingle($selections, $horseCount, $isOrdered),
            'box' => $this->expandBox($selections, $horseCount, $isOrdered),
            'nagashi' => $this->expandNagashi($selections, $horseCount, $isOrdered),
            'formation' => $this->expandFormation($selections, $horseCount, $isOrdered),
            default => [],
        };

        return $this->normalizeCombinations($combinations, $isOrdered);
    }

    /**
     * @param  array<string, mixed>  $selections
     * @return array<int, array<int, int>>
     */
    private function expandSingle(array $selections, int $horseCount, bool $isOrdered): array
    {
        $horses = $this->extractIntList($selections['horses'] ?? []);

        if ($horseCount === 1) {
            return array_map(static fn (int $h): array => [$h], $horses);
        }

        if (count($horses) !== $horseCount) {
            return [];
        }

        return [$horses];
    }

    /**
     * @param  array<string, mixed>  $selections
     * @return array<int, array<int, int>>
     */
    private function expandBox(array $selections, int $horseCount, bool $isOrdered): array
    {
        $horses = $this->extractIntList($selections['horses'] ?? []);

        if ($horseCount === 1) {
            return array_map(static fn (int $h): array => [$h], $horses);
        }

        if (count($horses) < $horseCount) {
            return [];
        }

        if ($isOrdered) {
            return $this->permutations($horses, $horseCount);
        }

        return $this->combinations($horses, $horseCount);
    }

    /**
     * @param  array<string, mixed>  $selections
     * @return array<int, array<int, int>>
     */
    private function expandNagashi(array $selections, int $horseCount, bool $isOrdered): array
    {
        $axis = $this->extractIntList($selections['axis'] ?? []);
        $others = $this->extractIntList($selections['others'] ?? []);

        if ($horseCount === 1) {
            return array_map(
                static fn (int $h): array => [$h],
                array_values(array_unique(array_merge($axis, $others)))
            );
        }

        if ($axis === [] || $others === []) {
            return [];
        }

        $axisNeeded = $horseCount - 1;
        if (count($axis) < $axisNeeded || count($axis) > $horseCount - 1) {
            // axis が 1 頭または 2 頭のパターンに対応するが、標準は axisNeeded = horseCount - 1。
            // 3連複で軸2頭の場合は axisNeeded = 2 に該当する。
        }

        // 軸の組み合わせ数 = horseCount - 1（相手が 1 頭）か、軸の頭数による。
        // nagashi の一般化: 軸は axis 全件をそのまま含め、残り (horseCount - count(axis)) 頭を others から選ぶ。
        $remainingSlots = $horseCount - count($axis);
        if ($remainingSlots < 1 || count($others) < $remainingSlots) {
            return [];
        }

        $otherCombinations = $this->combinations($others, $remainingSlots);

        $result = [];
        foreach ($otherCombinations as $otherCombo) {
            $combined = array_merge($axis, $otherCombo);
            if ($isOrdered) {
                // 着順考慮の nagashi は複数の着順パターンを生成する必要があるが、
                // 本実装では軸の着順を保持した配列として扱う。
                $result[] = $combined;
            } else {
                $result[] = $combined;
            }
        }

        return $result;
    }

    /**
     * @param  array<string, mixed>  $selections
     * @return array<int, array<int, int>>
     */
    private function expandFormation(array $selections, int $horseCount, bool $isOrdered): array
    {
        $columns = $selections['columns'] ?? [];
        if (! is_array($columns) || count($columns) !== $horseCount) {
            return [];
        }

        /** @var array<int, array<int, int>> $columnLists */
        $columnLists = [];
        foreach ($columns as $column) {
            $list = $this->extractIntList(is_array($column) ? $column : []);
            if ($list === []) {
                return [];
            }
            $columnLists[] = $list;
        }

        $result = [[]];
        foreach ($columnLists as $column) {
            $next = [];
            foreach ($result as $partial) {
                foreach ($column as $horse) {
                    if (in_array($horse, $partial, true)) {
                        continue;
                    }
                    $next[] = [...$partial, $horse];
                }
            }
            $result = $next;
        }

        return $result;
    }

    /**
     * 組み合わせ配列を券種の性質に応じて正規化し、重複を除去する。
     *
     * @param  array<int, array<int, int>>  $combinations
     * @return array<int, array<int, int>>
     */
    private function normalizeCombinations(array $combinations, bool $isOrdered): array
    {
        $seen = [];
        $normalized = [];
        foreach ($combinations as $combo) {
            $key = $isOrdered ? implode('-', $combo) : implode('-', $this->sortedCopy($combo));
            if (isset($seen[$key])) {
                continue;
            }
            $seen[$key] = true;
            $normalized[] = $isOrdered ? $combo : $this->sortedCopy($combo);
        }

        return $normalized;
    }

    /**
     * 展開済みの組み合わせと払い戻し結果を照合し、ヒットした金額を合算する。
     *
     * @param  array<int, array<int, int>>  $combinations
     * @param  array<int, array{amount: int, horse_numbers: array<int, int>}>  $payouts
     */
    private function sumMatchedPayouts(array $combinations, array $payouts, bool $isOrdered): int
    {
        if ($payouts === []) {
            return 0;
        }

        $total = 0;
        foreach ($payouts as $payout) {
            $target = $isOrdered ? $payout['horse_numbers'] : $this->sortedCopy($payout['horse_numbers']);
            foreach ($combinations as $combo) {
                if ($combo === $target) {
                    $total += $payout['amount'];
                    break;
                }
            }
        }

        return $total;
    }

    /**
     * @param  mixed  $value
     * @return array<int, int>
     */
    private function extractIntList(mixed $value): array
    {
        if (! is_array($value)) {
            return [];
        }

        $result = [];
        foreach ($value as $item) {
            if (is_int($item)) {
                $result[] = $item;
            } elseif (is_string($item) && ctype_digit($item)) {
                $result[] = (int) $item;
            }
        }

        return $result;
    }

    /**
     * nCk の組み合わせを生成する（順序不問）。
     *
     * @param  array<int, int>  $items
     * @return array<int, array<int, int>>
     */
    private function combinations(array $items, int $k): array
    {
        if ($k === 0) {
            return [[]];
        }
        if ($k > count($items)) {
            return [];
        }

        $result = [];
        $length = count($items);
        for ($i = 0; $i <= $length - $k; $i++) {
            $head = $items[$i];
            $tailCombinations = $this->combinations(array_slice($items, $i + 1), $k - 1);
            foreach ($tailCombinations as $tail) {
                $result[] = [$head, ...$tail];
            }
        }

        return $result;
    }

    /**
     * nPk の順列を生成する（順序あり）。
     *
     * @param  array<int, int>  $items
     * @return array<int, array<int, int>>
     */
    private function permutations(array $items, int $k): array
    {
        if ($k === 0) {
            return [[]];
        }

        $result = [];
        foreach ($items as $index => $item) {
            $rest = $items;
            array_splice($rest, $index, 1);
            foreach ($this->permutations($rest, $k - 1) as $tail) {
                $result[] = [$item, ...$tail];
            }
        }

        return $result;
    }

    /**
     * @param  array<int, int>  $items
     * @return array<int, int>
     */
    private function sortedCopy(array $items): array
    {
        $copy = $items;
        sort($copy);

        return $copy;
    }

    /**
     * JRA払い戻しテキストをパースし、各行を独立したエントリとして返す。
     * 複勝・ワイドなど複数行の券種は、各行がそれぞれ独立した払い戻し明細となる。
     *
     * @return array<int, array{ticket_type: string, horse_numbers: array<int, int>, amount: int, popularity: int}>
     *
     * @throws \InvalidArgumentException
     */
    private function parse(string $text): array
    {
        $lines = preg_split('/\r?\n/', trim($text));
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
            if (! ctype_digit($part) || $part === '') {
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
        if (! ctype_digit($cleaned) || $cleaned === '') {
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
        if (! ctype_digit($cleaned) || $cleaned === '') {
            throw new \InvalidArgumentException(
                sprintf('%d行目: 人気「%s」が不正です。', $lineNumber, $col)
            );
        }

        return (int) $cleaned;
    }

    /**
     * パース結果に全8券種が揃っていることを検証する。
     *
     * @param  array<int, array{ticket_type: string, horse_numbers: array<int, int>, amount: int, popularity: int}>  $entries
     *
     * @throws \InvalidArgumentException
     */
    private function validateAllTypesPresent(array $entries): void
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
}
