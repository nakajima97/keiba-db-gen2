<?php

namespace App\UseCases\HorseNote;

use App\Models\Horse;
use App\Models\HorseNote;
use App\Models\User;
use Carbon\CarbonInterface;

/**
 * 認証ユーザー所有のメモを指定競走馬について updated_at 降順で返す。
 * レース紐づきありのメモは race フィールドにレース情報を含む。
 */
class IndexAction
{
    /**
     * @return list<array{
     *     id: int,
     *     horse_id: int,
     *     race_id: int|null,
     *     race: array{uid: string, race_date: string, venue_name: string, race_number: int, race_name: string|null}|null,
     *     content: string,
     *     created_at: string,
     *     updated_at: string,
     * }>
     */
    public function execute(Horse $horse, User $user): array
    {
        return HorseNote::query()
            ->with(['race.venue'])
            ->where('user_id', $user->id)
            ->where('horse_id', $horse->id)
            ->orderBy('updated_at', 'desc')
            ->get()
            ->map(fn (HorseNote $note): array => $this->formatNote($note))
            ->all();
    }

    /**
     * @return array{
     *     id: int,
     *     horse_id: int,
     *     race_id: int|null,
     *     race: array{uid: string, race_date: string, venue_name: string, race_number: int, race_name: string|null}|null,
     *     content: string,
     *     created_at: string,
     *     updated_at: string,
     * }
     */
    private function formatNote(HorseNote $note): array
    {
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
    }
}
