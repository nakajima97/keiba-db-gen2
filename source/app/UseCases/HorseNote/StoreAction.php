<?php

namespace App\UseCases\HorseNote;

use App\Models\Horse;
use App\Models\HorseNote;
use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Validation\ValidationException;

/**
 * 認証ユーザー所有の競走馬メモを 1 件作成する。
 * 同一ユーザー × 同一馬 × 同一 race_id（null 含む）の組み合わせで既にメモが存在する場合は ValidationException。
 */
class StoreAction
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
     *
     * @throws ValidationException
     */
    public function execute(Horse $horse, User $user, ?int $raceId, string $content): array
    {
        $duplicateExists = HorseNote::query()
            ->where('user_id', $user->id)
            ->where('horse_id', $horse->id)
            ->where(function ($query) use ($raceId) {
                if ($raceId === null) {
                    $query->whereNull('race_id');
                } else {
                    $query->where('race_id', $raceId);
                }
            })
            ->exists();

        if ($duplicateExists) {
            throw ValidationException::withMessages([
                'content' => 'この競走馬・レースの組み合わせには既にメモが存在します。',
            ]);
        }

        $note = HorseNote::create([
            'user_id' => $user->id,
            'horse_id' => $horse->id,
            'race_id' => $raceId,
            'content' => $content,
        ]);

        $note->load(['race.venue']);

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
