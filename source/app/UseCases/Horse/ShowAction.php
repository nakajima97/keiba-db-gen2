<?php

namespace App\UseCases\Horse;

use App\Models\Horse;
use Carbon\CarbonInterface;

/**
 * 競走馬とそのレース履歴（出走したレース・競馬場含む）を取得し、競走馬詳細画面の表示用データを返す。
 */
class ShowAction
{
    /**
     * @return array{
     *     id: int,
     *     name: string,
     *     birth_year: int|null,
     *     race_histories: list<array{
     *         race_uid: string,
     *         race_date: string,
     *         venue_name: string,
     *         race_number: int,
     *         race_name: string|null,
     *         finishing_order: int,
     *         jockey_name: string,
     *         popularity: int,
     *     }>,
     * }
     */
    public function execute(Horse $horse): array
    {
        $horse->load(['raceResultHorses.race.venue']);

        $raceHistories = $horse->raceResultHorses
            ->sortBy([
                ['race.race_date', 'desc'],
                ['race.race_number', 'asc'],
            ])
            ->map(fn ($result) => [
                'race_uid' => $result->race->uid,
                'race_date' => $result->race->race_date instanceof CarbonInterface
                    ? $result->race->race_date->format('Y-m-d')
                    : (string) $result->race->race_date,
                'venue_name' => $result->race->venue->name,
                'race_number' => (int) $result->race->race_number,
                'race_name' => $result->race->race_name,
                'finishing_order' => (int) $result->finishing_order,
                'jockey_name' => $result->jockey_name,
                'popularity' => (int) $result->popularity,
            ])
            ->values()
            ->all();

        return [
            'id' => $horse->id,
            'name' => $horse->name,
            'birth_year' => $horse->birth_year !== null ? (int) $horse->birth_year : null,
            'race_histories' => $raceHistories,
        ];
    }
}
