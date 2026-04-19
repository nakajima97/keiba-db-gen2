<?php

namespace App\UseCases\TicketPurchase;

/**
 * TicketPurchase の selections を、券種・買い方に応じた有効な組み合わせ配列に展開する。
 *
 * フォーメーションで同一馬番が複数列に入る場合は、無効な組み合わせを除外する。
 * 順序を持つ券種（馬単・三連単）は順列として、それ以外は組み合わせとして展開する。
 */
class ExpandSelectionsAction
{
    /** 着順を保持する券種 */
    private const ORDERED_TYPES = ['umatan', 'sanrentan'];

    /** 券種ごとの照合対象馬番数 */
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
     * 券種名・買い方名・selections から有効な組み合わせの配列を返す。
     * 未対応の券種・組み合わせが成立しない場合は空配列を返す。
     *
     * @param  array<string, mixed>|null  $selections
     * @return array<int, array<int, int>>
     */
    public function execute(string $ticketTypeName, string $buyTypeName, ?array $selections): array
    {
        $horseCount = self::TICKET_TYPE_HORSE_COUNT[$ticketTypeName] ?? null;
        if ($horseCount === null) {
            return [];
        }

        $isOrdered = in_array($ticketTypeName, self::ORDERED_TYPES, true);
        $selections = $selections ?? [];

        $combinations = match ($buyTypeName) {
            'single' => $this->expandSingle($selections, $horseCount),
            'box' => $this->expandBox($selections, $horseCount, $isOrdered),
            'nagashi' => $this->expandNagashi($selections, $horseCount),
            'formation' => $this->expandFormation($selections, $horseCount),
            default => [],
        };

        return $this->normalizeCombinations($combinations, $isOrdered);
    }

    /**
     * @param  array<string, mixed>  $selections
     * @return array<int, array<int, int>>
     */
    private function expandSingle(array $selections, int $horseCount): array
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
    private function expandNagashi(array $selections, int $horseCount): array
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

        $remainingSlots = $horseCount - count($axis);
        if ($remainingSlots < 1 || count($others) < $remainingSlots) {
            return [];
        }

        $otherCombinations = $this->combinations($others, $remainingSlots);

        $result = [];
        foreach ($otherCombinations as $otherCombo) {
            $result[] = array_merge($axis, $otherCombo);
        }

        return $result;
    }

    /**
     * @param  array<string, mixed>  $selections
     * @return array<int, array<int, int>>
     */
    private function expandFormation(array $selections, int $horseCount): array
    {
        // col1/col2/col3 形式（旧フォーマット）を columns 形式に正規化
        if (isset($selections['col1'])) {
            $cols = array_values(array_filter(
                [$selections['col1'] ?? [], $selections['col2'] ?? [], $selections['col3'] ?? []],
                fn (array $col): bool => $col !== [],
            ));
            $selections = ['columns' => $cols];
        }

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
     * @param  array<int, array<int, int>>  $combinations
     * @return array<int, array<int, int>>
     */
    private function normalizeCombinations(array $combinations, bool $isOrdered): array
    {
        $seen = [];
        $normalized = [];
        foreach ($combinations as $combo) {
            // 同一馬番が複数ポジションに入る組み合わせは無効
            if (count($combo) !== count(array_unique($combo))) {
                continue;
            }
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
}
