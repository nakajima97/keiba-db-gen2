<?php

namespace App\UseCases\Insights;

use App\UseCases\TicketPurchase\ExpandSelectionsAction;
use Illuminate\Support\Facades\DB;

/**
 * 振り返り画面（馬連流し）の各種集計値を返す。
 *
 * 集計対象は `buy_type=nagashi` × `ticket_type=umaren` で、レース結果（race_result_horses）が
 * 入力済みのもののみ。期間（1m/3m/6m/1y/all）で `races.race_date` を絞り込む。
 *
 * 4パターン分類は軸馬（selections.axis）と相手馬（selections.others）が
 * `finishing_order in (1, 2)` に含まれているかの組み合わせで決まる:
 * - hit: 軸も相手も少なくとも1頭が1〜2着
 * - axis_only: 軸のみ1〜2着
 * - others_only: 相手のみ1〜2着
 * - miss: 軸も相手も1〜2着にゼロ
 *
 * 軸馬の人気帯は軸馬中で最も人気の良い（popularity が最小の）馬で決まる:
 * - top: 1〜3
 * - mid: 4〜6
 * - low: 7以上
 * 全軸馬の popularity が取得できないチケットは popularity 系の集計（Matrix/Returns）から除外する。
 */
class ShowInsightsAction
{
    public function __construct(
        private ExpandSelectionsAction $expandSelections,
    ) {}

    /**
     * @return array{
     *   summary: array{
     *     total_tickets: int,
     *     total_purchase_amount: int,
     *     total_payout_amount: int,
     *     return_rate: float,
     *     hit_rate: float,
     *   }|null,
     *   patternBreakdown: list<array{
     *     pattern: string,
     *     count: int,
     *     ratio: float,
     *   }>,
     *   popularityPatternMatrix: list<array{
     *     popularity: string,
     *     pattern: string,
     *     count: int,
     *     ratio: float,
     *   }>,
     *   popularityReturns: list<array{
     *     popularity: string,
     *     count: int,
     *     purchase_amount: int,
     *     payout_amount: int,
     *     return_rate: float,
     *   }>,
     *   monthlyTrends: list<array{
     *     month: string,
     *     count: int,
     *     hit_rate: float,
     *     return_rate: float,
     *   }>,
     *   recentSamples: list<array{
     *     ticket_id: int,
     *     race_uid: string,
     *     race_date: string,
     *     venue_name: string,
     *     race_number: int,
     *     axis_horse_numbers: list<int>,
     *     axis_best_finishing_order: int|null,
     *     others_best_finishing_order: int|null,
     *     pattern: string,
     *     purchase_amount: int,
     *     payout_amount: int,
     *   }>,
     * }
     */
    public function execute(int $userId, string $period): array
    {
        $startDate = $this->resolveStartDate($period);

        $query = DB::table('ticket_purchases')
            ->join('races', 'ticket_purchases.race_id', '=', 'races.id')
            ->join('venues', 'races.venue_id', '=', 'venues.id')
            ->join('ticket_types', 'ticket_purchases.ticket_type_id', '=', 'ticket_types.id')
            ->join('buy_types', 'ticket_purchases.buy_type_id', '=', 'buy_types.id')
            ->where('ticket_purchases.user_id', $userId)
            ->where('buy_types.name', 'nagashi')
            ->where('ticket_types.name', 'umaren')
            ->whereExists(function ($sub) {
                $sub->select(DB::raw(1))
                    ->from('race_result_horses')
                    ->whereColumn('race_result_horses.race_id', 'races.id');
            });

        if ($startDate !== null) {
            $query->where('races.race_date', '>=', $startDate);
        }

        $rows = $query
            ->orderByDesc('races.race_date')
            ->orderByDesc('ticket_purchases.id')
            ->select([
                'ticket_purchases.id as ticket_id',
                'ticket_purchases.selections',
                'ticket_purchases.unit_stake',
                'ticket_purchases.payout_amount',
                'ticket_purchases.ticket_type_id',
                'ticket_purchases.buy_type_id',
                'races.id as race_id',
                'races.uid as race_uid',
                'races.race_date',
                'races.race_number',
                'venues.name as venue_name',
            ])
            ->get();

        if ($rows->isEmpty()) {
            return [
                'summary' => null,
                'patternBreakdown' => $this->emptyPatternBreakdown(),
                'popularityPatternMatrix' => $this->emptyPopularityPatternMatrix(),
                'popularityReturns' => $this->emptyPopularityReturns(),
                'monthlyTrends' => [],
                'recentSamples' => [],
            ];
        }

        $raceIds = $rows->pluck('race_id')->unique()->values()->all();

        // race_id ごとに 1〜2着の horse_number と horse_number→popularity マップを構築
        /** @var array<int, list<int>> $topTwoByRace */
        $topTwoByRace = [];
        /** @var array<int, array<int, int>> $popularityByRace */
        $popularityByRace = [];

        $resultHorses = DB::table('race_result_horses')
            ->whereIn('race_id', $raceIds)
            ->select(['race_id', 'horse_number', 'finishing_order', 'popularity'])
            ->get();

        foreach ($resultHorses as $rh) {
            $raceId = (int) $rh->race_id;
            $horseNumber = (int) $rh->horse_number;
            $finishingOrder = (int) $rh->finishing_order;
            $popularity = (int) $rh->popularity;

            $popularityByRace[$raceId][$horseNumber] = $popularity;

            if ($finishingOrder === 1 || $finishingOrder === 2) {
                $topTwoByRace[$raceId][] = $horseNumber;
            }
        }

        // race_id ごとの horse_number → finishing_order マップ（recentSamples 用）
        /** @var array<int, array<int, int>> $finishingByRace */
        $finishingByRace = [];
        foreach ($resultHorses as $rh) {
            $finishingByRace[(int) $rh->race_id][(int) $rh->horse_number] = (int) $rh->finishing_order;
        }

        // 1チケットずつ集計のための中間データを構築
        $tickets = [];
        foreach ($rows as $row) {
            $selections = json_decode((string) $row->selections, true);
            $axis = $this->extractIntList(is_array($selections) ? ($selections['axis'] ?? []) : []);
            $others = $this->extractIntList(is_array($selections) ? ($selections['others'] ?? []) : []);

            $raceId = (int) $row->race_id;
            $topTwo = $topTwoByRace[$raceId] ?? [];

            $axisHit = ! empty(array_intersect($axis, $topTwo));
            $othersHit = ! empty(array_intersect($others, $topTwo));

            $pattern = match (true) {
                $axisHit && $othersHit => 'hit',
                $axisHit && ! $othersHit => 'axis_only',
                ! $axisHit && $othersHit => 'others_only',
                default => 'miss',
            };

            // 購入額: unit_stake * 有効点数
            $unitStake = $row->unit_stake !== null ? (int) $row->unit_stake : 0;
            $combinations = $this->expandSelections->execute('umaren', 'nagashi', is_array($selections) ? $selections : null);
            $purchaseAmount = $unitStake * count($combinations);
            $payoutAmount = $row->payout_amount !== null ? (int) $row->payout_amount : 0;

            // 軸馬の最良 popularity を算出（取得できないものは無視）
            $axisPopularities = [];
            foreach ($axis as $horseNumber) {
                if (isset($popularityByRace[$raceId][$horseNumber])) {
                    $axisPopularities[] = $popularityByRace[$raceId][$horseNumber];
                }
            }
            $popularityBand = null;
            if ($axisPopularities !== []) {
                $bestPopularity = min($axisPopularities);
                $popularityBand = match (true) {
                    $bestPopularity <= 3 => 'top',
                    $bestPopularity <= 6 => 'mid',
                    default => 'low',
                };
            }

            // 軸/相手の最良 finishing_order
            $axisBestFinishing = null;
            foreach ($axis as $horseNumber) {
                if (isset($finishingByRace[$raceId][$horseNumber])) {
                    $order = $finishingByRace[$raceId][$horseNumber];
                    if ($axisBestFinishing === null || $order < $axisBestFinishing) {
                        $axisBestFinishing = $order;
                    }
                }
            }
            $othersBestFinishing = null;
            foreach ($others as $horseNumber) {
                if (isset($finishingByRace[$raceId][$horseNumber])) {
                    $order = $finishingByRace[$raceId][$horseNumber];
                    if ($othersBestFinishing === null || $order < $othersBestFinishing) {
                        $othersBestFinishing = $order;
                    }
                }
            }

            $tickets[] = [
                'ticket_id' => (int) $row->ticket_id,
                'race_uid' => (string) $row->race_uid,
                'race_date' => (string) $row->race_date,
                'venue_name' => (string) $row->venue_name,
                'race_number' => (int) $row->race_number,
                'axis_horse_numbers' => $axis,
                'axis_best_finishing_order' => $axisBestFinishing,
                'others_best_finishing_order' => $othersBestFinishing,
                'pattern' => $pattern,
                'purchase_amount' => $purchaseAmount,
                'payout_amount' => $payoutAmount,
                'popularity_band' => $popularityBand,
            ];
        }

        $totalTickets = count($tickets);
        $totalPurchase = array_sum(array_column($tickets, 'purchase_amount'));
        $totalPayout = array_sum(array_column($tickets, 'payout_amount'));
        $hitCount = count(array_filter($tickets, static fn (array $t): bool => $t['pattern'] === 'hit'));

        $summary = [
            'total_tickets' => $totalTickets,
            'total_purchase_amount' => (int) $totalPurchase,
            'total_payout_amount' => (int) $totalPayout,
            'return_rate' => $totalPurchase > 0 ? round($totalPayout / $totalPurchase * 100, 1) : 0.0,
            'hit_rate' => $totalTickets > 0 ? round($hitCount / $totalTickets * 100, 1) : 0.0,
        ];

        // 4パターン分解
        $patternBreakdown = [];
        foreach (['hit', 'axis_only', 'others_only', 'miss'] as $pattern) {
            $count = count(array_filter($tickets, static fn (array $t): bool => $t['pattern'] === $pattern));
            $patternBreakdown[] = [
                'pattern' => $pattern,
                'count' => $count,
                'ratio' => $totalTickets > 0 ? round($count / $totalTickets * 100, 1) : 0.0,
            ];
        }

        // popularity 系（band が取れたチケットのみ対象）
        $popularityTickets = array_values(array_filter(
            $tickets,
            static fn (array $t): bool => $t['popularity_band'] !== null,
        ));

        $popularityPatternMatrix = [];
        foreach (['top', 'mid', 'low'] as $band) {
            $bandTickets = array_filter(
                $popularityTickets,
                static fn (array $t): bool => $t['popularity_band'] === $band,
            );
            $bandTotal = count($bandTickets);
            foreach (['hit', 'axis_only', 'others_only', 'miss'] as $pattern) {
                $count = count(array_filter(
                    $bandTickets,
                    static fn (array $t): bool => $t['pattern'] === $pattern,
                ));
                $popularityPatternMatrix[] = [
                    'popularity' => $band,
                    'pattern' => $pattern,
                    'count' => $count,
                    'ratio' => $bandTotal > 0 ? round($count / $bandTotal * 100, 1) : 0.0,
                ];
            }
        }

        $popularityReturns = [];
        foreach (['top', 'mid', 'low'] as $band) {
            $bandTickets = array_filter(
                $popularityTickets,
                static fn (array $t): bool => $t['popularity_band'] === $band,
            );
            $bandPurchase = array_sum(array_column($bandTickets, 'purchase_amount'));
            $bandPayout = array_sum(array_column($bandTickets, 'payout_amount'));
            $popularityReturns[] = [
                'popularity' => $band,
                'count' => count($bandTickets),
                'purchase_amount' => (int) $bandPurchase,
                'payout_amount' => (int) $bandPayout,
                'return_rate' => $bandPurchase > 0 ? round($bandPayout / $bandPurchase * 100, 1) : 0.0,
            ];
        }

        // 月別推移
        /** @var array<string, array{count: int, hit: int, purchase: int, payout: int}> $monthMap */
        $monthMap = [];
        foreach ($tickets as $ticket) {
            $month = substr($ticket['race_date'], 0, 7);
            if (! isset($monthMap[$month])) {
                $monthMap[$month] = ['count' => 0, 'hit' => 0, 'purchase' => 0, 'payout' => 0];
            }
            $monthMap[$month]['count']++;
            if ($ticket['pattern'] === 'hit') {
                $monthMap[$month]['hit']++;
            }
            $monthMap[$month]['purchase'] += $ticket['purchase_amount'];
            $monthMap[$month]['payout'] += $ticket['payout_amount'];
        }
        ksort($monthMap);
        $monthlyTrends = [];
        foreach ($monthMap as $month => $values) {
            $monthlyTrends[] = [
                'month' => $month,
                'count' => $values['count'],
                'hit_rate' => $values['count'] > 0 ? round($values['hit'] / $values['count'] * 100, 1) : 0.0,
                'return_rate' => $values['purchase'] > 0 ? round($values['payout'] / $values['purchase'] * 100, 1) : 0.0,
            ];
        }

        // 直近10件（rows は既に race_date DESC, ticket_purchases.id DESC で取得済み）
        $recentSamples = [];
        foreach (array_slice($tickets, 0, 10) as $ticket) {
            $recentSamples[] = [
                'ticket_id' => $ticket['ticket_id'],
                'race_uid' => $ticket['race_uid'],
                'race_date' => $ticket['race_date'],
                'venue_name' => $ticket['venue_name'],
                'race_number' => $ticket['race_number'],
                'axis_horse_numbers' => $ticket['axis_horse_numbers'],
                'axis_best_finishing_order' => $ticket['axis_best_finishing_order'],
                'others_best_finishing_order' => $ticket['others_best_finishing_order'],
                'pattern' => $ticket['pattern'],
                'purchase_amount' => $ticket['purchase_amount'],
                'payout_amount' => $ticket['payout_amount'],
            ];
        }

        return [
            'summary' => $summary,
            'patternBreakdown' => $patternBreakdown,
            'popularityPatternMatrix' => $popularityPatternMatrix,
            'popularityReturns' => $popularityReturns,
            'monthlyTrends' => $monthlyTrends,
            'recentSamples' => $recentSamples,
        ];
    }

    private function resolveStartDate(string $period): ?string
    {
        return match ($period) {
            '1m' => now()->subMonth()->toDateString(),
            '3m' => now()->subMonths(3)->toDateString(),
            '6m' => now()->subMonths(6)->toDateString(),
            '1y' => now()->subYear()->toDateString(),
            'all' => null,
            default => now()->subMonth()->toDateString(),
        };
    }

    /**
     * @return list<array{pattern: string, count: int, ratio: float}>
     */
    private function emptyPatternBreakdown(): array
    {
        $result = [];
        foreach (['hit', 'axis_only', 'others_only', 'miss'] as $pattern) {
            $result[] = ['pattern' => $pattern, 'count' => 0, 'ratio' => 0.0];
        }

        return $result;
    }

    /**
     * @return list<array{popularity: string, pattern: string, count: int, ratio: float}>
     */
    private function emptyPopularityPatternMatrix(): array
    {
        $result = [];
        foreach (['top', 'mid', 'low'] as $band) {
            foreach (['hit', 'axis_only', 'others_only', 'miss'] as $pattern) {
                $result[] = [
                    'popularity' => $band,
                    'pattern' => $pattern,
                    'count' => 0,
                    'ratio' => 0.0,
                ];
            }
        }

        return $result;
    }

    /**
     * @return list<array{popularity: string, count: int, purchase_amount: int, payout_amount: int, return_rate: float}>
     */
    private function emptyPopularityReturns(): array
    {
        $result = [];
        foreach (['top', 'mid', 'low'] as $band) {
            $result[] = [
                'popularity' => $band,
                'count' => 0,
                'purchase_amount' => 0,
                'payout_amount' => 0,
                'return_rate' => 0.0,
            ];
        }

        return $result;
    }

    /**
     * @return list<int>
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
}
