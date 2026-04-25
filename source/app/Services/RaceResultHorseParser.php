<?php

namespace App\Services;

/**
 * JRA公式サイトの着順テキストをパースし、各馬のレース結果データを抽出する。
 *
 * ヘッダー行（列名）は自動的にスキップされる。
 * 1頭ごとのデータは以下の2形式に対応:
 *
 * 通常形式（3行）:
 *   行1: 着順\t枠（枠N色）\t馬番\t馬名\t性齢\t負担重量\t騎手名\tタイム\t着差\t
 *   行2: コーナー通過順位（スペース区切り）
 *   行3: 推定上り\t馬体重（増減）\t調教師名\t単勝人気
 *
 * 注記あり形式（5行、ブリンカー着用等）:
 *   行1: 着順\t枠（枠N色）\t馬番\t
 *   行2: 馬名（注記を含む）
 *   行3: 性齢\t負担重量\t騎手名\tタイム\t着差\t
 *   行4: コーナー通過順位（スペース区切り）
 *   行5: 推定上り\t馬体重（増減）\t調教師名\t単勝人気
 *
 * @throws \InvalidArgumentException パースに失敗した場合
 */
class RaceResultHorseParser
{
    /** 馬体重と増減（例: "500(+2)", "490(初出走)", "510(0)", "480(-4)") */
    private const HORSE_WEIGHT_PATTERN = '/^(\d+)\(([^)]*)\)$/u';

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
     * @param  array<int, string>  $block
     *
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
        return match (count($block)) {
            2 => $this->parseTwoLineBlock($block, $horseIndex),
            3 => $this->parseThreeLineBlock($block, $horseIndex),
            5 => $this->parseFiveLineBlock($block, $horseIndex),
            default => throw new \InvalidArgumentException(
                sprintf('%d頭目: 行数が不正です（%d行）。', $horseIndex, count($block))
            ),
        };
    }

    /**
     * 2行形式（旧フォーマット）: 行1に全データ10列、行2に上り等。
     *
     * @param  array<int, string>  $block
     *
     * @throws \InvalidArgumentException
     */
    private function parseTwoLineBlock(array $block, int $horseIndex): array
    {
        $cols1 = explode("\t", $block[0]);
        $cols2 = explode("\t", $block[1]);

        if (count($cols1) < 10) {
            throw new \InvalidArgumentException(
                sprintf('%d頭目の1行目: 列数が不足しています（期待: 10列以上、実際: %d列）。', $horseIndex, count($cols1))
            );
        }

        if (count($cols2) < 4) {
            throw new \InvalidArgumentException(
                sprintf('%d頭目の2行目: 列数が不足しています（期待: 4列以上、実際: %d列）。', $horseIndex, count($cols2))
            );
        }

        $horseName = trim($cols1[3]);
        if ($horseName === '') {
            throw new \InvalidArgumentException(sprintf('%d頭目: 馬名が空です。', $horseIndex));
        }

        [$horseWeight, $horseWeightChange] = $this->parseHorseWeight(trim($cols2[1]), $horseIndex);

        return $this->buildEntry(
            finishingOrder: $this->parseIntColumn($cols1[0], $horseIndex, '着順'),
            frameNumber: $this->parseFrameNumber($cols1[1], $horseIndex),
            horseNumber: $this->parseIntColumn($cols1[2], $horseIndex, '馬番'),
            horseName: $horseName,
            sexAge: trim($cols1[4]),
            weight: $this->parseFloatColumn($cols1[5], $horseIndex, '負担重量'),
            jockeyName: trim($cols1[6]),
            raceTime: trim($cols1[7]),
            timeDifference: $this->parseNullableString(trim($cols1[8])),
            cornerOrder: $this->parseNullableString(trim($cols1[9])),
            estimatedPace: $this->parseNullableFloat(trim($cols2[0])),
            horseWeight: $horseWeight,
            horseWeightChange: $horseWeightChange,
            trainerName: trim($cols2[2]),
            popularity: $this->parseIntColumn($cols2[3], $horseIndex, '単勝人気'),
        );
    }

    /**
     * 3行形式（通常JRAフォーマット）: 行1=主データ、行2=コーナー通過、行3=上り等。
     *
     * @param  array<int, string>  $block
     *
     * @throws \InvalidArgumentException
     */
    private function parseThreeLineBlock(array $block, int $horseIndex): array
    {
        $cols1 = explode("\t", $block[0]);
        $cols3 = explode("\t", $block[2]);

        if (count($cols1) < 8) {
            throw new \InvalidArgumentException(
                sprintf('%d頭目の1行目: 列数が不足しています（期待: 8列以上、実際: %d列）。', $horseIndex, count($cols1))
            );
        }

        if (count($cols3) < 4) {
            throw new \InvalidArgumentException(
                sprintf('%d頭目の3行目: 列数が不足しています（期待: 4列以上、実際: %d列）。', $horseIndex, count($cols3))
            );
        }

        $horseName = trim($cols1[3]);
        if ($horseName === '') {
            throw new \InvalidArgumentException(sprintf('%d頭目: 馬名が空です。', $horseIndex));
        }

        [$horseWeight, $horseWeightChange] = $this->parseHorseWeight(trim($cols3[1]), $horseIndex);

        return $this->buildEntry(
            finishingOrder: $this->parseIntColumn($cols1[0], $horseIndex, '着順'),
            frameNumber: $this->parseFrameNumber($cols1[1], $horseIndex),
            horseNumber: $this->parseIntColumn($cols1[2], $horseIndex, '馬番'),
            horseName: $horseName,
            sexAge: trim($cols1[4]),
            weight: $this->parseFloatColumn($cols1[5], $horseIndex, '負担重量'),
            jockeyName: trim($cols1[6]),
            raceTime: trim($cols1[7]),
            timeDifference: $this->parseNullableString(trim($cols1[8] ?? '')),
            cornerOrder: $this->parseNullableString(trim($block[1])),
            estimatedPace: $this->parseNullableFloat(trim($cols3[0])),
            horseWeight: $horseWeight,
            horseWeightChange: $horseWeightChange,
            trainerName: trim($cols3[2]),
            popularity: $this->parseIntColumn($cols3[3], $horseIndex, '単勝人気'),
        );
    }

    /**
     * 5行形式（注記あり馬）: 行1=着順/枠/馬番、行2=馬名、行3=性齢以降、行4=コーナー、行5=上り等。
     *
     * @param  array<int, string>  $block
     *
     * @throws \InvalidArgumentException
     */
    private function parseFiveLineBlock(array $block, int $horseIndex): array
    {
        $cols1 = explode("\t", $block[0]);
        $cols3 = explode("\t", $block[2]);
        $cols5 = explode("\t", $block[4]);

        if (count($cols1) < 3) {
            throw new \InvalidArgumentException(
                sprintf('%d頭目の1行目: 列数が不足しています（期待: 3列以上、実際: %d列）。', $horseIndex, count($cols1))
            );
        }

        if (count($cols3) < 4) {
            throw new \InvalidArgumentException(
                sprintf('%d頭目の3行目: 列数が不足しています（期待: 4列以上、実際: %d列）。', $horseIndex, count($cols3))
            );
        }

        if (count($cols5) < 4) {
            throw new \InvalidArgumentException(
                sprintf('%d頭目の5行目: 列数が不足しています（期待: 4列以上、実際: %d列）。', $horseIndex, count($cols5))
            );
        }

        $horseName = trim($block[1]);
        if ($horseName === '') {
            throw new \InvalidArgumentException(sprintf('%d頭目: 馬名が空です。', $horseIndex));
        }

        [$horseWeight, $horseWeightChange] = $this->parseHorseWeight(trim($cols5[1]), $horseIndex);

        return $this->buildEntry(
            finishingOrder: $this->parseIntColumn($cols1[0], $horseIndex, '着順'),
            frameNumber: $this->parseFrameNumber($cols1[1], $horseIndex),
            horseNumber: $this->parseIntColumn($cols1[2], $horseIndex, '馬番'),
            horseName: $horseName,
            sexAge: trim($cols3[0]),
            weight: $this->parseFloatColumn($cols3[1], $horseIndex, '負担重量'),
            jockeyName: trim($cols3[2]),
            raceTime: trim($cols3[3]),
            timeDifference: $this->parseNullableString(trim($cols3[4] ?? '')),
            cornerOrder: $this->parseNullableString(trim($block[3])),
            estimatedPace: $this->parseNullableFloat(trim($cols5[0])),
            horseWeight: $horseWeight,
            horseWeightChange: $horseWeightChange,
            trainerName: trim($cols5[2]),
            popularity: $this->parseIntColumn($cols5[3], $horseIndex, '単勝人気'),
        );
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
     * 馬体重文字列（例: "500(+2)", "490(初出走)", "510(0)"）をパースする。
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
