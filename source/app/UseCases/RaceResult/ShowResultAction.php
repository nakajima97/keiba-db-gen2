<?php

namespace App\UseCases\RaceResult;

use App\Models\Race;

/**
 * uid でレースを取得し、レース結果確認・編集画面の表示用データ（払戻情報を含む）を返す。
 */
class ShowResultAction
{
    /**
     * @return array{
     *     uid: string,
     *     venue_name: string,
     *     race_date: string,
     *     race_number: int,
     *     payouts: list<array{
     *         ticket_type_label: string,
     *         ticket_type_name: string,
     *         payout_amount: int,
     *         popularity: int,
     *         horses: list<array{horse_number: int, sort_order: int}>,
     *     }>,
     *     finishing_horses: list<array{
     *         finishing_order: int,
     *         frame_number: int,
     *         horse_number: int,
     *         horse_name: string,
     *         jockey_name: string,
     *         race_time: string,
     *     }>,
     * }
     */
    public function execute(string $uid): array
    {
        $race = Race::where('uid', $uid)
            ->with([
                'venue',
                'racePayouts' => function ($query) {
                    $query->join('ticket_types', 'race_payouts.ticket_type_id', '=', 'ticket_types.id')
                        ->orderBy('ticket_types.sort_order')
                        ->select('race_payouts.*');
                },
                'racePayouts.ticketType',
                'racePayouts.racePayoutHorses' => function ($query) {
                    $query->orderBy('sort_order');
                },
                'raceResultHorses' => function ($query) {
                    $query->orderBy('finishing_order');
                },
            ])
            ->firstOrFail();

        $payouts = $race->racePayouts->map(function ($payout) {
            return [
                'ticket_type_label' => $payout->ticketType->label,
                'ticket_type_name' => $payout->ticketType->name,
                'payout_amount' => $payout->payout_amount,
                'popularity' => $payout->popularity,
                'horses' => $payout->racePayoutHorses->map(function ($horse) {
                    return [
                        'horse_number' => $horse->horse_number,
                        'sort_order' => $horse->sort_order,
                    ];
                })->values()->all(),
            ];
        })->values()->all();

        $finishingHorses = $race->raceResultHorses->map(function ($horse) {
            return [
                'finishing_order' => $horse->finishing_order,
                'frame_number' => $horse->frame_number,
                'horse_number' => $horse->horse_number,
                'horse_name' => $horse->horse_name,
                'jockey_name' => $horse->jockey_name,
                'race_time' => $horse->race_time,
            ];
        })->values()->all();

        return [
            'uid' => $race->uid,
            'venue_name' => $race->venue->name,
            'race_date' => $race->race_date,
            'race_number' => $race->race_number,
            'payouts' => $payouts,
            'finishing_horses' => $finishingHorses,
        ];
    }
}
