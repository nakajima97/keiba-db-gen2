<?php

namespace App\Services;

use Carbon\Carbon;

/**
 * JRA出馬表テキストをパースし、各馬のレースエントリ情報を抽出する。
 *
 * 1頭ごとのブロックは「枠N色\tM(\t)?」の行で始まり、
 * 馬名・馬体重・性齢・負担重量・騎手名を含む複数行で構成される。
 * ブリンカー着用行は馬名行の直前に挿入されうるためスキップする。
 */
class RaceEntryParser
{
    /** 枠番と馬番を含むヘッダ行（例: "枠1白\t1\t"） */
    private const FRAME_LINE_PATTERN = '/^枠(\d+)(?:[白黒赤青黄緑橙桃]+)?\t(\d+)/u';

    /** 馬体重（例: "426kg(-2)", "500kg(初出走)", "500kg(0)") */
    private const HORSE_WEIGHT_PATTERN = '/^(\d+)kg\(/u';

    /** 性齢（例: "牝3/黒鹿", "牡5/栗毛", "セ4/鹿毛") */
    private const SEX_AGE_PATTERN = '/^[牡牝セせん]\s*(\d+)\s*\//u';

    /** 負担重量（例: "55.0kg"） */
    private const WEIGHT_PATTERN = '/^(\d+(?:\.\d+)?)kg\s*$/u';

    /**
     * 出馬表テキストをパースし、各馬のデータを配列で返す。
     *
     * @return array<int, array{
     *     horse_name: string,
     *     birth_year: int,
     *     jockey_name: string,
     *     frame_number: int,
     *     horse_number: int,
     *     weight: float,
     *     horse_weight: ?int,
     * }>
     */
    public function parse(string $text, Carbon $raceDate): array
    {
        $lines = preg_split('/\r?\n/', $text);
        if ($lines === false) {
            return [];
        }

        $raceYear = (int) $raceDate->year;

        /** @var array<int, array<int, string>> $blocks */
        $blocks = [];
        $currentBlock = null;

        foreach ($lines as $line) {
            $trimmed = rtrim($line);

            if (preg_match(self::FRAME_LINE_PATTERN, $trimmed)) {
                if ($currentBlock !== null) {
                    $blocks[] = $currentBlock;
                }
                $currentBlock = [$trimmed];

                continue;
            }

            if ($currentBlock !== null) {
                $currentBlock[] = $trimmed;
            }
        }

        if ($currentBlock !== null) {
            $blocks[] = $currentBlock;
        }

        $entries = [];
        foreach ($blocks as $block) {
            $entry = $this->parseBlock($block, $raceYear);
            if ($entry !== null) {
                $entries[] = $entry;
            }
        }

        return $entries;
    }

    /**
     * 1頭分のテキストブロックをパースする。
     *
     * @param  array<int, string>  $block
     * @return array{
     *     horse_name: string,
     *     birth_year: int,
     *     jockey_name: string,
     *     frame_number: int,
     *     horse_number: int,
     *     weight: float,
     *     horse_weight: ?int,
     * }|null
     */
    private function parseBlock(array $block, int $raceYear): ?array
    {
        $frameNumber = null;
        $horseNumber = null;
        $horseName = null;
        $horseWeight = null;
        $birthYear = null;
        $weight = null;
        $jockeyName = null;

        $weightLineIndex = null;

        foreach ($block as $index => $rawLine) {
            $line = trim($rawLine);

            if ($line === '') {
                continue;
            }

            if ($frameNumber === null && preg_match(self::FRAME_LINE_PATTERN, $rawLine, $matches)) {
                $frameNumber = (int) $matches[1];
                $horseNumber = (int) $matches[2];

                continue;
            }

            if ($horseName === null) {
                if ($line === 'ブリンカー着用') {
                    continue;
                }
                $horseName = $line;

                continue;
            }

            if ($horseWeight === null && preg_match(self::HORSE_WEIGHT_PATTERN, $line, $matches)) {
                $horseWeight = (int) $matches[1];

                continue;
            }

            if ($birthYear === null && preg_match(self::SEX_AGE_PATTERN, $line, $matches)) {
                $age = (int) $matches[1];
                $birthYear = $raceYear - $age;

                continue;
            }

            if ($weight === null && preg_match(self::WEIGHT_PATTERN, $line, $matches)) {
                $weight = (float) $matches[1];
                $weightLineIndex = $index;

                continue;
            }

            if ($weight !== null && $jockeyName === null) {
                $jockeyName = $line;

                continue;
            }
        }

        if (
            $frameNumber === null
            || $horseNumber === null
            || $horseName === null
            || $birthYear === null
            || $weight === null
            || $jockeyName === null
        ) {
            return null;
        }

        return [
            'horse_name' => $horseName,
            'birth_year' => $birthYear,
            'jockey_name' => $jockeyName,
            'frame_number' => $frameNumber,
            'horse_number' => $horseNumber,
            'weight' => $weight,
            'horse_weight' => $horseWeight,
        ];
    }
}
