<?php

namespace App\Services;

/**
 * 調教師名の文字列を「調教師名」と「所属」に分解するユーティリティ。
 *
 * JRA 出馬表では調教師が `加藤 征弘(美浦)` のように所属を括弧書きで連結した形で表示される。
 * 所属の括弧（全角・半角）が末尾にあればそれを所属として切り出し、残りを調教師名とする。
 */
class TrainerNameParser
{
    /** 末尾に所属の括弧書きを持つ調教師名（例: "加藤 征弘(美浦)", "田中 義人（栗東）"） */
    private const NAME_WITH_AFFILIATION_PATTERN = '/^(?<name>[^()（）]+?)\s*[(（](?<affiliation>[^()（）]+)[)）]$/u';

    /**
     * 調教師名の文字列を分解する。
     *
     * @return array{name: string, affiliation: string|null}|null 空文字の場合は null
     */
    public static function parse(string $text): ?array
    {
        $trimmed = trim($text);
        if ($trimmed === '') {
            return null;
        }

        if (preg_match(self::NAME_WITH_AFFILIATION_PATTERN, $trimmed, $matches)) {
            return [
                'name' => trim($matches['name']),
                'affiliation' => trim($matches['affiliation']),
            ];
        }

        return [
            'name' => $trimmed,
            'affiliation' => null,
        ];
    }

    /**
     * 文字列が「調教師名(所属)」の形式かどうかを判定する。
     */
    public static function hasAffiliation(string $text): bool
    {
        return preg_match(self::NAME_WITH_AFFILIATION_PATTERN, trim($text)) === 1;
    }
}
