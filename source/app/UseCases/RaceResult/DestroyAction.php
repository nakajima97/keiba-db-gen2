<?php

namespace App\UseCases\RaceResult;

use App\Exceptions\RaceResult\NoResultToDestroyException;
use App\Models\Race;
use App\Models\TicketPurchase;
use Illuminate\Support\Facades\DB;

/**
 * 指定レースの結果（着順・払戻）と、関連する馬券の払戻金額をリセットする。
 *
 * race_result_horses と race_payouts （race_payout_horses は cascadeOnDelete で連鎖削除）を削除し、
 * 同レースの ticket_purchases.payout_amount をユーザー問わず NULL に戻す。
 */
class DestroyAction
{
    /**
     * @throws NoResultToDestroyException 削除対象の着順・払戻が一切存在しない場合
     */
    public function execute(string $uid): void
    {
        $race = Race::where('uid', $uid)->firstOrFail();

        $hasResultHorses = $race->raceResultHorses()->exists();
        $hasPayouts = $race->racePayouts()->exists();

        if (! $hasResultHorses && ! $hasPayouts) {
            throw new NoResultToDestroyException('このレースには削除対象の結果がありません。');
        }

        DB::transaction(function () use ($race): void {
            $race->racePayouts()->delete();
            $race->raceResultHorses()->delete();

            TicketPurchase::where('race_id', $race->id)
                ->update(['payout_amount' => null]);
        });
    }
}
