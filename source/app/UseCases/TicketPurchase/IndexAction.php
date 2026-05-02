<?php

namespace App\UseCases\TicketPurchase;

use App\Models\TicketPurchase;

/**
 * 認証ユーザーの馬券購入一覧（カーソルページネーション付き）を返す。
 *
 * JOIN クエリ・金額計算・ExpandSelectionsAction 呼び出しを集約し、
 * Controller から切り出すことで薄いコントローラー設計を保つ。
 */
class IndexAction
{
    public function __construct(
        private ExpandSelectionsAction $expandSelections,
    ) {}

    /**
     * @return array{
     *   purchases: list<array{
     *     id: int,
     *     race_uid: string|null,
     *     has_race_result: bool,
     *     race_date: string|null,
     *     venue_name: string|null,
     *     race_number: int|null,
     *     ticket_type_label: string,
     *     buy_type_name: string,
     *     selections: array<mixed>|null,
     *     num_combinations: int,
     *     unit_stake: int|null,
     *     payout_amount: int|null,
     *   }>,
     *   nextCursor: string|null,
     * }
     */
    public function execute(int $userId): array
    {
        $paginator = TicketPurchase::query()
            ->where('ticket_purchases.user_id', $userId)
            ->leftJoin('races', 'ticket_purchases.race_id', '=', 'races.id')
            ->leftJoin('venues', 'races.venue_id', '=', 'venues.id')
            ->join('ticket_types', 'ticket_purchases.ticket_type_id', '=', 'ticket_types.id')
            ->join('buy_types', 'ticket_purchases.buy_type_id', '=', 'buy_types.id')
            ->select([
                'ticket_purchases.id',
                'ticket_purchases.selections',
                'ticket_purchases.unit_stake',
                'ticket_purchases.payout_amount',
                'races.uid as race_uid',
                'races.race_date',
                'venues.name as venue_name',
                'races.race_number',
                'ticket_types.name as ticket_type_name',
                'ticket_types.label as ticket_type_label',
                'buy_types.name as buy_type_name',
            ])
            ->selectRaw('EXISTS(SELECT 1 FROM race_payouts WHERE race_payouts.race_id = races.id) as has_race_result')
            ->orderByDesc('race_date')
            ->orderByDesc('venue_name')
            ->orderByDesc('race_number')
            ->cursorPaginate(30);

        $purchases = $paginator->map(function (TicketPurchase $purchase): array {
            $numCombinations = count($this->expandSelections->execute(
                $purchase->ticket_type_name,
                $purchase->buy_type_name,
                $purchase->selections,
            ));

            return [
                'id' => $purchase->id,
                'race_uid' => $purchase->race_uid,
                'has_race_result' => (bool) $purchase->has_race_result,
                'race_date' => $purchase->race_date,
                'venue_name' => $purchase->venue_name,
                'race_number' => $purchase->race_number,
                'ticket_type_label' => $purchase->ticket_type_label,
                'buy_type_name' => $purchase->buy_type_name,
                'selections' => $purchase->selections,
                'num_combinations' => $numCombinations,
                'unit_stake' => $purchase->unit_stake !== null ? $purchase->unit_stake * $numCombinations : null,
                'payout_amount' => $purchase->payout_amount !== null ? (int) $purchase->payout_amount : null,
            ];
        });

        return [
            'purchases' => $purchases->all(),
            'nextCursor' => $paginator->nextCursor()?->encode(),
        ];
    }
}
