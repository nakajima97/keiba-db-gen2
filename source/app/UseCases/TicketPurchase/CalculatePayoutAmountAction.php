<?php

namespace App\UseCases\TicketPurchase;

use App\Models\RacePayout;
use App\Models\TicketPurchase;
use Illuminate\Database\Eloquent\Collection;

/**
 * TicketPurchase の払い戻し金額を計算する。
 *
 * race_payouts が存在しない場合・amount が null の場合は null を返す。
 * 計算式: ヒット組み合わせの払戻合計（JRA公表・100円あたり）× (amount / (組み合わせ数 × 100))
 */
class CalculatePayoutAmountAction
{
    public function __construct(private readonly ExpandSelectionsAction $expandSelections) {}

    /**
     * 対象の TicketPurchase の払い戻し金額を計算して返す。
     * race_payouts が存在しない場合・amount が null の場合は null を返す。
     */
    public function execute(TicketPurchase $purchase): ?int
    {
        if ($purchase->amount === null) {
            return null;
        }

        $purchase->loadMissing(['ticketType', 'buyType']);

        $ticketTypeName = $purchase->ticketType->name;
        $buyTypeName = $purchase->buyType->name;
        $selections = $purchase->selections;
        $isOrdered = in_array($ticketTypeName, ['umatan', 'sanrentan'], true);

        $combinations = $this->expandSelections->execute($ticketTypeName, $buyTypeName, $selections);

        if ($combinations === []) {
            return null;
        }

        /** @var Collection<int, RacePayout> $payouts */
        $payouts = RacePayout::with(['ticketType', 'racePayoutHorses' => function ($query): void {
            $query->orderBy('sort_order');
        }])->where('race_id', $purchase->race_id)->get();

        if ($payouts->isEmpty()) {
            return null;
        }

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

        $totalPayout = $this->sumMatchedPayouts(
            $combinations,
            $payoutsByType[$ticketTypeName] ?? [],
            $isOrdered,
        );

        if ($totalPayout <= 0) {
            return null;
        }

        $numCombinations = count($combinations);

        return (int) ($totalPayout * $purchase->amount / ($numCombinations * 100));
    }

    /**
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
