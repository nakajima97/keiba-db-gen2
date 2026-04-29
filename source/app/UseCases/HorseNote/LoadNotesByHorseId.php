<?php

namespace App\UseCases\HorseNote;

use App\Models\HorseNote;
use App\Models\User;

/**
 * 指定レースに関連する馬IDリストに対して、認証ユーザーのメモを馬IDごとに 1 件ずつ取得する。
 * 同一馬で race-linked と horse-linked が共存する場合は race-linked を優先する。
 */
class LoadNotesByHorseId
{
    /**
     * @param  list<int>  $horseIds
     * @return array<int, array{id: int, content: string, source: string}>
     */
    public function execute(User $user, array $horseIds, int $raceId): array
    {
        if ($horseIds === []) {
            return [];
        }

        $notes = HorseNote::query()
            ->where('user_id', $user->id)
            ->whereIn('horse_id', $horseIds)
            ->where(function ($query) use ($raceId) {
                $query->where('race_id', $raceId)->orWhereNull('race_id');
            })
            ->get();

        $byHorseId = [];
        foreach ($notes as $note) {
            $horseId = (int) $note->horse_id;
            $isRaceLinked = $note->race_id !== null;
            $candidate = [
                'id' => (int) $note->id,
                'content' => $note->content,
                'source' => $isRaceLinked ? 'race' : 'horse',
            ];

            // race-linked メモを優先。既に race-linked が入っていれば上書きしない。
            if (isset($byHorseId[$horseId]) && $byHorseId[$horseId]['source'] === 'race') {
                continue;
            }

            $byHorseId[$horseId] = $candidate;
        }

        return $byHorseId;
    }
}
