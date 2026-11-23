<?php

namespace App\Services;

/**
 * JRA公式サイトの着順テキストをパースし、各馬のレース結果データを抽出する。
 *
 * ヘッダー行（列名）は自動的にスキップされる。
 * 1頭分のデータは「数字\t」で始まる行から次の同様の行までで、論理的には以下の列を持つ:
 *   着順, 枠（枠N色）, 馬番, 馬名, 性齢, 負担重量, 騎手名, タイム, 着差, コーナー通過順位,
 *   推定上り, 馬体重（増減）, 調教師名, 単勝人気, [Rt]
 *
 * コピー時の改行の入り方により1〜6行程度に分かれる。例:
 *   通常（3行）:     "…タイム\t着差\t" / "コーナー通過順位" / "推定上り\t馬体重\t調教師名\t単勝人気"
 *   注記あり（5行）: "着順\t枠\t馬番\t" / "馬名ブリンカー着用" / "性齢\t…\t着差\t" / "コーナー" / "推定上り…"
 *   直線コース（1行）: コーナー通過順位が空のため全列が1行に並ぶ
 *   レコード:        着差セルの「レコード」が独立した行になる
 *
 * @throws \InvalidArgumentException パースに失敗した場合
 */
class RaceResultHorseParser
{
    /** 馬体重と増減（例: "500", "500(+2)", "490(初出走)", "510(0)", "480(-4)") */
    private const HORSE_WEIGHT_PATTERN = '/^(\d+)(?:\(([^)]*)\))?$/u';

    /** コーナー通過順位（例: "2 2 2 1", "3", "2-2-1"） */
    private const CORNER_ORDER_PATTERN = '/^[0-9]+(?:[ \-][0-9]+)*$/';

    /** 着順〜タイムまでの先頭セル数 */
    private const HEAD_CELL_COUNT = 8;

    /** 馬名セルの末尾に連結される注記 */
    private const BLINKER_SUFFIX = 'ブリンカー着用';

    /**
     * 着順テキストをパースし、各馬のデータを配列で返す。
     *
     * @return array<int, array{
     *     finishing_order: int,
     *     frame_number: int,
     *     horse_number: int,
     *     horse_name: string,
     *     sex_age: string,
     *     weight: float,
     *     jockey_name: string,
     *     race_time: string,
     *     time_difference: string|null,
     *     corner_order: string|null,
     *     estimated_pace: float|null,
     *     horse_weight: int|null,
     *     horse_weight_change: int|null,
     *     trainer_name: string,
     *     popularity: int,
     * }>
     *
     * @throws \InvalidArgumentException
     */
    public function parse(string $text): array
    {
        $lines = preg_split('/\r?\n/', trim($text));
        if ($lines === false || $lines === []) {
            throw new \InvalidArgumentException('テキストが空です。');
        }

        // ヘッダー行をスキップ（最初に "数字\t" で始まる行まで）
        $startIdx = null;
        foreach ($lines as $i => $line) {
            if (preg_match('/^\d+\t/', $line)) {
                $startIdx = $i;
                break;
            }
        }

        if ($startIdx === null) {
            throw new \InvalidArgumentException('テキストが空です。');
        }

        $lines = array_slice($lines, $startIdx);

        // 馬ブロックに分割（"数字\t" で始まる行が新しい馬の開始）
        $blocks = [];
        $current = [];
        foreach ($lines as $line) {
            if ($current !== [] && preg_match('/^\d+\t/', $line)) {
                $blocks[] = $current;
                $current = [rtrim($line)];
            } elseif (trim($line) !== '') {
                $current[] = rtrim($line);
            }
        }
        if ($current !== []) {
            $blocks[] = $current;
        }

        if ($blocks === []) {
            throw new \InvalidArgumentException('テキストが空です。');
        }

        $entries = [];
        foreach ($blocks as $index => $block) {
            $entries[] = $this->parseBlock($block, $index + 1);
        }

        return $entries;
    }

    /**
     * 馬1頭分のブロック（行の配列）をパースする。
     *
     * JRA 公式サイトからのコピーは、ブロック要素を含むセル（馬名の注記・着差の「レコード」・
     * コーナー通過順位など）の前後で改行が入り、空セルが消えることがあるため行数が一定しない。
     * そこでブロックの全行をセルの並びに平坦化し、先頭8セル（着順〜タイム）と
     * 末尾4〜5セル（推定上り〜単勝人気、Rt）を固定位置として読み、
     * 間に残ったセルを着差・コーナー通過順位として解釈する。
     *
     * @param  array<int, string>  $block
     * @return array{
     *     finishing_order: int,
     *     frame_number: int,
     *     horse_number: int,
     *     horse_name: string,
     *     sex_age: string,
     *     weight: float,
     *     jockey_name: string,
     *     race_time: string,
     *     time_difference: string|null,
     *     corner_order: string|null,
     *     estimated_pace: float|null,
     *     horse_weight: int|null,
     *     horse_weight_change: int|null,
     *     trainer_name: string,
     *     popularity: int,
     * }
     *
     * @throws \InvalidArgumentException
     */
    private function parseBlock(array $block, int $horseIndex): array
    {
        $cells = [];
        foreach ($block as $line) {
            foreach (explode("\t", $line) as $cell) {
                $cells[] = trim($cell);
            }
        }

        // 末尾が「単勝人気\tRt」なら Rt 列あり（調教師名は数字のみにならない）
        $count = count($cells);
        $hasRating = $count >= 2
            && ctype_digit($cells[$count - 1])
            && ctype_digit($cells[$count - 2]);
        $tailLength = $hasRating ? 5 : 4;

        $middleLength = $count - self::HEAD_CELL_COUNT - $tailLength;
        if ($middleLength < 0 || $middleLength > 2) {
            throw new \InvalidArgumentException(
                sprintf('%d頭目: 列数が不正です（%d列）。', $horseIndex, $count)
            );
        }

        $head = array_slice($cells, 0, self::HEAD_CELL_COUNT);
        $middle = array_slice($cells, self::HEAD_CELL_COUNT, $middleLength);
        $tail = array_slice($cells, self::HEAD_CELL_COUNT + $middleLength, 4);

        [$timeDifference, $cornerOrder] = $this->parseMiddleCells($middle);

        $horseName = $this->normalizeHorseName($head[3]);
        if ($horseName === '') {
            throw new \InvalidArgumentException(sprintf('%d頭目: 馬名が空です。', $horseIndex));
        }

        [$horseWeight, $horseWeightChange] = $this->parseHorseWeight($tail[1], $horseIndex);

        return $this->buildEntry(
            finishingOrder: $this->parseIntColumn($head[0], $horseIndex, '着順'),
            frameNumber: $this->parseFrameNumber($head[1], $horseIndex),
            horseNumber: $this->parseIntColumn($head[2], $horseIndex, '馬番'),
            horseName: $horseName,
            sexAge: $head[4],
            weight: $this->parseFloatColumn($head[5], $horseIndex, '負担重量'),
            jockeyName: $head[6],
            raceTime: $head[7],
            timeDifference: $timeDifference,
            cornerOrder: $cornerOrder,
            estimatedPace: $this->parseNullableFloat($tail[0]),
            horseWeight: $horseWeight,
            horseWeightChange: $horseWeightChange,
            trainerName: $tail[2],
            popularity: $this->parseIntColumn($tail[3], $horseIndex, '単勝人気'),
        );
    }

    /**
     * タイムと推定上りの間に残ったセル（0〜2個）を着差・コーナー通過順位に振り分ける。
     *
     * 2個なら順に着差・コーナー通過順位。1個の場合はコピー時に片方の空セルが消えているため、
     * コーナー通過順位の形式（例: "2 2 2 1", "3", "2-2-1"）に一致すればコーナー通過順位、
     * それ以外（例: "クビ", "0.3", "レコード"）は着差とみなす。
     *
     * @param  array<int, string>  $middle
     * @return array{0: string|null, 1: string|null}
     */
    private function parseMiddleCells(array $middle): array
    {
        if (count($middle) === 2) {
            return [$this->parseNullableString($middle[0]), $this->parseNullableString($middle[1])];
        }

        if (count($middle) === 1) {
            $value = $middle[0];
            if (preg_match(self::CORNER_ORDER_PATTERN, $value)) {
                return [null, $value];
            }

            return [$this->parseNullableString($value), null];
        }

        return [null, null];
    }

    /**
     * 馬名から JRA 注記 prefix（マルガイ等）と末尾の「ブリンカー着用」を除去する。
     */
    private function normalizeHorseName(string $name): string
    {
        $name = trim($name);
        if (str_ends_with($name, self::BLINKER_SUFFIX) && $name !== self::BLINKER_SUFFIX) {
            $name = trim(mb_substr($name, 0, mb_strlen($name) - mb_strlen(self::BLINKER_SUFFIX)));
        }

        return HorseNameNormalizer::stripJraAnnotationPrefix($name);
    }

    /**
     * @return array{
     *     finishing_order: int,
     *     frame_number: int,
     *     horse_number: int,
     *     horse_name: string,
     *     sex_age: string,
     *     weight: float,
     *     jockey_name: string,
     *     race_time: string,
     *     time_difference: string|null,
     *     corner_order: string|null,
     *     estimated_pace: float|null,
     *     horse_weight: int|null,
     *     horse_weight_change: int|null,
     *     trainer_name: string,
     *     popularity: int,
     * }
     */
    private function buildEntry(
        int $finishingOrder,
        int $frameNumber,
        int $horseNumber,
        string $horseName,
        string $sexAge,
        float $weight,
        string $jockeyName,
        string $raceTime,
        ?string $timeDifference,
        ?string $cornerOrder,
        ?float $estimatedPace,
        ?int $horseWeight,
        ?int $horseWeightChange,
        string $trainerName,
        int $popularity,
    ): array {
        return [
            'finishing_order' => $finishingOrder,
            'frame_number' => $frameNumber,
            'horse_number' => $horseNumber,
            'horse_name' => $horseName,
            'sex_age' => $sexAge,
            'weight' => $weight,
            'jockey_name' => $jockeyName,
            'race_time' => $raceTime,
            'time_difference' => $timeDifference,
            'corner_order' => $cornerOrder,
            'estimated_pace' => $estimatedPace,
            'horse_weight' => $horseWeight,
            'horse_weight_change' => $horseWeightChange,
            'trainer_name' => $trainerName,
            'popularity' => $popularity,
        ];
    }

    /**
     * 枠番文字列（"枠8桃" や "2" など）から枠番の数値を抽出する。
     *
     * @throws \InvalidArgumentException
     */
    private function parseFrameNumber(string $col, int $horseIndex): int
    {
        $col = trim($col);
        if (ctype_digit($col) && $col !== '') {
            return (int) $col;
        }
        if (preg_match('/^枠(\d+)/', $col, $m)) {
            return (int) $m[1];
        }
        throw new \InvalidArgumentException(
            sprintf('%d頭目: 枠番「%s」が不正です。', $horseIndex, $col)
        );
    }

    /**
     * 馬体重文字列（例: "500", "500(+2)", "490(初出走)", "510(0)"）をパースする。
     *
     * @return array{0: int|null, 1: int|null}
     *
     * @throws \InvalidArgumentException
     */
    private function parseHorseWeight(string $col, int $horseIndex): array
    {
        if ($col === '') {
            return [null, null];
        }

        if (! preg_match(self::HORSE_WEIGHT_PATTERN, $col, $matches)) {
            throw new \InvalidArgumentException(
                sprintf('%d頭目: 馬体重「%s」の形式が不正です。', $horseIndex, $col)
            );
        }

        $horseWeight = (int) $matches[1];

        if (! isset($matches[2])) {
            return [$horseWeight, null];
        }

        $changeStr = $matches[2];

        if ($changeStr === '初出走') {
            return [$horseWeight, null];
        }

        $cleaned = ltrim($changeStr, '+');
        if (! is_numeric($cleaned)) {
            throw new \InvalidArgumentException(
                sprintf('%d頭目: 馬体重増減「%s」の形式が不正です。', $horseIndex, $col)
            );
        }

        return [$horseWeight, (int) $cleaned];
    }

    /** @throws \InvalidArgumentException */
    private function parseIntColumn(string $col, int $horseIndex, string $fieldName): int
    {
        $trimmed = trim($col);
        if (! ctype_digit($trimmed) || $trimmed === '') {
            throw new \InvalidArgumentException(
                sprintf('%d頭目: %s「%s」が不正です。', $horseIndex, $fieldName, $col)
            );
        }

        return (int) $trimmed;
    }

    /** @throws \InvalidArgumentException */
    private function parseFloatColumn(string $col, int $horseIndex, string $fieldName): float
    {
        $trimmed = trim($col);
        if (! is_numeric($trimmed) || $trimmed === '') {
            throw new \InvalidArgumentException(
                sprintf('%d頭目: %s「%s」が不正です。', $horseIndex, $fieldName, $col)
            );
        }

        return (float) $trimmed;
    }

    private function parseNullableString(string $col): ?string
    {
        return $col === '' ? null : $col;
    }

    private function parseNullableFloat(string $col): ?float
    {
        if ($col === '' || ! is_numeric($col)) {
            return null;
        }

        return (float) $col;
    }
}
