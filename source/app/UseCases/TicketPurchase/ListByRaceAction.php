<?php

namespace App\UseCases\TicketPurchase;

use App\Models\TicketPurchase;

/**
 * 認証ユーザー × 指定レースに紐づく馬券購入一覧を、レース結果画面の表示用スキーマで返す。
 *
 * IndexAction と同様に JOIN クエリで ticket_types / buy_types を取得し、
 * ExpandSelectionsAction で組み合わせ数を算出して purchase_amount を計算する。
 *
 * フロントの RaceMyTicketSection は selections を horses / axis(+others) / columns の 3 形式しか受け付けないため、
 * DB に保存されうる axis1/axis2/others・col1/col2/col3 形式をここで正規化する。
 */
class ListByRaceAction
{
    public function __construct(
        private ExpandSelectionsAction $expandSelections,
    ) {}

    /**
     * @return list<array{
     *   id: int,
     *   ticket_type_label: string,
     *   buy_type_name: string,
     *   buy_type_label: string,
     *   selections: array<mixed>|null,
     *   purchase_amount: int|null,
     *   payout_amount: int|null,
     * }>
     */
    public function execute(int $userId, int $raceId): array
    {
        $purchases = TicketPurchase::query()
            ->where('ticket_purchases.user_id', $userId)
            ->where('ticket_purchases.race_id', $raceId)
            ->join('ticket_types', 'ticket_purchases.ticket_type_id', '=', 'ticket_types.id')
            ->join('buy_types', 'ticket_purchases.buy_type_id', '=', 'buy_types.id')
            ->select([
                'ticket_purchases.id',
                'ticket_purchases.selections',
                'ticket_purchases.unit_stake',
                'ticket_purchases.payout_amount',
                'ticket_types.name as ticket_type_name',
                'ticket_types.label as ticket_type_label',
                'buy_types.name as buy_type_name',
                'buy_types.label as buy_type_label',
            ])
            ->orderBy('ticket_purchases.id')
            ->get();

        return $purchases->map(function (TicketPurchase $purchase): array {
            $numCombinations = count($this->expandSelections->execute(
                $purchase->ticket_type_name,
                $purchase->buy_type_name,
                $purchase->selections,
            ));

            return [
                'id' => (int) $purchase->id,
                'ticket_type_label' => $purchase->ticket_type_label,
                'buy_type_name' => $purchase->buy_type_name,
                'buy_type_label' => $purchase->buy_type_label,
                'selections' => $this->normalizeSelections($purchase->selections),
                'purchase_amount' => $purchase->unit_stake !== null ? (int) $purchase->unit_stake * $numCombinations : null,
                'payout_amount' => $purchase->payout_amount !== null ? (int) $purchase->payout_amount : null,
            ];
        })->all();
    }

    /**
     * selections を horses / axis(+others) / columns の 3 形式に正規化する。
     *
     * @param  array<string, mixed>|null  $selections
     * @return array<string, mixed>|null
     */
    private function normalizeSelections(?array $selections): ?array
    {
        if ($selections === null) {
            return null;
        }

        // col1/col2/col3 形式（sanrentan/nagashi・formation 旧フォーマット）→ columns
        if (array_key_exists('col1', $selections)) {
            $cols = array_values(array_filter(
                [
                    $selections['col1'] ?? [],
                    $selections['col2'] ?? [],
                    $selections['col3'] ?? [],
                ],
                fn ($col): bool => is_array($col) && $col !== [],
            ));

            return ['columns' => $cols];
        }

        // axis1/axis2/others 形式（2 軸流し）→ axis(+others) に集約
        if (array_key_exists('axis1', $selections)) {
            $axis1 = is_array($selections['axis1'] ?? null) ? $selections['axis1'] : [];
            $axis2 = is_array($selections['axis2'] ?? null) ? $selections['axis2'] : [];
            $axis = array_values(array_merge($axis1, $axis2));

            $result = ['axis' => $axis];
            $others = $selections['others'] ?? null;
            if (is_array($others) && $others !== []) {
                $result['others'] = array_values($others);
            }

            return $result;
        }

        return $selections;
    }
}
