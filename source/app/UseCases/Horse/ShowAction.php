<?php

namespace App\UseCases\Horse;

use App\Models\Horse;
use App\Models\HorseNote;
use App\Models\User;
use Carbon\CarbonInterface;

/**
 * 競走馬とそのレース履歴（出走したレース・競馬場含む）と認証ユーザーのメモを取得し、
 * 競走馬詳細画面の表示用データを返す。
 */
class ShowAction
{
    /**
     * @return array{
     *     id: int,
     *     name: string,
     *     birth_year: int|null,
     *     race_histories: list<array{
     *         race_id: int,
     *         race_uid: string,
     *         race_date: string,
     *         venue_name: string,
     *         race_number: int,
     *         race_name: string|null,
     *         finishing_order: int,
     *         jockey_name: string,
     *         popularity: int,
     *     }>,
     *     notes: list<array{
     *         id: int,
     *         horse_id: int,
     *         race_id: int|null,
     *         race: array{uid: string, race_date: string, venue_name: string, race_number: int, race_name: string|null}|null,
     *         content: string,
     *         created_at: string,
     *         updated_at: string,
     *     }>,
     * }
     */
    public function execute(Horse $horse, User $user): array
    {
        $horse->load([
            'raceResultHorses' => function ($query) {
                $query->join('races', 'race_result_horses.race_id', '=', 'races.id')
                    ->orderBy('races.race_date', 'desc')
                    ->orderBy('races.race_number', 'asc')
                    ->select('race_result_horses.*');
            },
            'raceResultHorses.race.venue',
        ]);

        $raceHistories = $horse->raceResultHorses
            ->map(fn ($result) => [
                'race_id' => (int) $result->race->id,
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

        $notes = HorseNote::query()
            ->with(['race.venue'])
            ->where('user_id', $user->id)
            ->where('horse_id', $horse->id)
            ->orderBy('updated_at', 'desc')
            ->get()
            ->map(function (HorseNote $note): array {
                $race = null;
                if ($note->race !== null) {
                    $race = [
                        'uid' => $note->race->uid,
                        'race_date' => $note->race->race_date instanceof CarbonInterface
                            ? $note->race->race_date->format('Y-m-d')
                            : (string) $note->race->race_date,
                        'venue_name' => $note->race->venue->name,
                        'race_number' => (int) $note->race->race_number,
                        'race_name' => $note->race->race_name,
                    ];
                }

                return [
                    'id' => (int) $note->id,
                    'horse_id' => (int) $note->horse_id,
                    'race_id' => $note->race_id !== null ? (int) $note->race_id : null,
                    'race' => $race,
                    'content' => $note->content,
                    'created_at' => $note->created_at instanceof CarbonInterface
                        ? $note->created_at->toIso8601String()
                        : (string) $note->created_at,
                    'updated_at' => $note->updated_at instanceof CarbonInterface
                        ? $note->updated_at->toIso8601String()
                        : (string) $note->updated_at,
                ];
            })
            ->values()
            ->all();

        return [
            'id' => $horse->id,
            'name' => $horse->name,
            'birth_year' => $horse->birth_year !== null ? (int) $horse->birth_year : null,
            'race_histories' => $raceHistories,
            'notes' => $notes,
        ];
    }
}
