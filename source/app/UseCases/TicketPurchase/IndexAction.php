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
     *     amount: int|null,
     *     payout_amount: int|null,
     *   }>,
     *   nextCursor: string|null,
     * }
     */
    public function execute(int $userId): array
    {
        // TODO: TicketPurchaseController::index から移行する
        throw new \LogicException('Not implemented');
    }
}
