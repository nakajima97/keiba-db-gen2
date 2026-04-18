<?php

namespace App\UseCases\Race;

use App\Models\Race;
use Carbon\CarbonInterface;

/**
 * レースと出馬表（馬・騎手含む）を取得し、レース詳細画面の表示用データを返す。
 */
class ShowAction
{
    /**
     * @return array{
     *     uid: string,
     *     race_date: string,
     *     venue_name: string,
     *     race_number: int,
     *     entries: array<int, array{
     *         frame_number: int,
     *         horse_number: int,
     *         horse_name: string,
     *         jockey_name: string,
     *         weight: int|null
     *     }>
     * }
     */
    public function execute(Race $race): array
    {
        $race->load([
            'venue',
            'raceEntries' => fn ($query) => $query->orderBy('horse_number'),
            'raceEntries.horse',
            'raceEntries.jockey',
        ]);

        return [
            'uid' => $race->uid,
            'race_date' => $race->race_date instanceof CarbonInterface
                ? $race->race_date->format('Y-m-d')
                : (string) $race->race_date,
            'venue_name' => $race->venue->name,
            'race_number' => (int) $race->race_number,
            'entries' => $race->raceEntries->map(fn ($entry) => [
                'frame_number' => (int) $entry->frame_number,
                'horse_number' => (int) $entry->horse_number,
                'horse_name' => $entry->horse->name,
                'jockey_name' => $entry->jockey->name,
                'weight' => $entry->horse_weight !== null ? (int) $entry->horse_weight : null,
            ])->all(),
        ];
    }
}
