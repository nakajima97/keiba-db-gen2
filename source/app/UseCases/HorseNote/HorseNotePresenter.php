<?php

namespace App\UseCases\HorseNote;

use App\Models\HorseNote;
use App\Models\Race;
use Carbon\CarbonInterface;

/**
 * HorseNote モデルを API レスポンス用の配列に整形する。
 * race リレーションを含めて返すため、呼び出し側で `with('race.venue')` を済ませておくこと。
 */
class HorseNotePresenter
{
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
    public static function present(HorseNote $note): array
    {
        return [
            'id' => (int) $note->id,
            'horse_id' => (int) $note->horse_id,
            'race_id' => $note->race_id !== null ? (int) $note->race_id : null,
            'race' => self::presentRace($note->race),
            'content' => $note->content,
            'created_at' => $note->created_at instanceof CarbonInterface
                ? $note->created_at->toIso8601String()
                : (string) $note->created_at,
            'updated_at' => $note->updated_at instanceof CarbonInterface
                ? $note->updated_at->toIso8601String()
                : (string) $note->updated_at,
        ];
    }

    /**
     * @return array{uid: string, race_date: string, venue_name: string, race_number: int, race_name: string|null}|null
     */
    private static function presentRace(?Race $race): ?array
    {
        if ($race === null) {
            return null;
        }

        return [
            'uid' => $race->uid,
            'race_date' => $race->race_date instanceof CarbonInterface
                ? $race->race_date->format('Y-m-d')
                : (string) $race->race_date,
            'venue_name' => $race->venue->name,
            'race_number' => (int) $race->race_number,
            'race_name' => $race->race_name,
        ];
    }
}
