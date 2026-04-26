<?php

namespace App\UseCases\RaceResult;

use App\Models\Race;

/**
 * uid でレースを取得し、レース結果画面の表示用データを返す。
 */
class ShowAction
{
    /**
     * @return array{uid: string, venue_name: string, race_date: string, race_number: int, has_existing_result: bool}
     */
    public function execute(string $uid): array
    {
        $race = Race::where('uid', $uid)->with('venue')->firstOrFail();

        return [
            'uid' => $race->uid,
            'venue_name' => $race->venue->name,
            'race_date' => $race->race_date,
            'race_number' => $race->race_number,
            'has_existing_result' => $race->raceResultHorses()->exists(),
        ];
    }
}
