<?php

namespace App\UseCases\RaceResult;

use App\Exceptions\RaceResult\ParseException;
use App\Models\Race;
use App\Models\RacePayout;
use App\Models\RacePayoutHorse;
use App\Models\RaceResultHorse;
use App\Models\TicketPurchase;
use App\Models\TicketType;
use App\Services\RaceResultHorseParser;
use App\UseCases\TicketPurchase\CalculatePayoutAmountAction;
use Illuminate\Database\Eloquent\Collection;
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

    public function __construct(
        private readonly CalculatePayoutAmountAction $calculatePayoutAmountAction,
        private readonly RaceResultHorseParser $raceResultHorseParser,
    ) {}

    /**
     * @param  array{text: string, result_text: string}  $data
     */
    public function execute(array $data, string $uid, int $userId): void
    {
        $race = Race::where('uid', $uid)->firstOrFail();
        $raceId = $race->id;

        try {
            $resultHorseEntries = $this->raceResultHorseParser->parse($data['result_text']);
        } catch (\InvalidArgumentException $e) {
            throw new ParseException($e->getMessage(), 'result_text');
        }

        try {
            $entries = $this->parse($data['text']);
            $this->validateAllTypesPresent($entries);
        } catch (\InvalidArgumentException $e) {
            throw new ParseException($e->getMessage(), 'text');
        }

        $ticketTypeIds = TicketType::whereIn('name', self::REQUIRED_TYPES)
            ->pluck('id', 'name')
            ->all();

        DB::transaction(function () use ($entries, $resultHorseEntries, $raceId, $ticketTypeIds, $userId): void {
            foreach ($resultHorseEntries as $horseEntry) {
                RaceResultHorse::create(array_merge(['race_id' => $raceId], $horseEntry));
            }

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
     */
    private function updateTicketPurchasesPayoutAmount(int $raceId, int $userId): void
    {
        /** @var Collection<int, TicketPurchase> $purchases */
        $purchases = TicketPurchase::with(['ticketType', 'buyType'])
            ->where('race_id', $raceId)
            ->where('user_id', $userId)
            ->get();

        foreach ($purchases as $purchase) {
            $payoutAmount = $this->calculatePayoutAmountAction->execute($purchase);
            if ($payoutAmount !== null) {
                $purchase->payout_amount = $payoutAmount;
                $purchase->save();
            }
        }
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
