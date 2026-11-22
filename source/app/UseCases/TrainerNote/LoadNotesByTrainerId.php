<?php

namespace App\UseCases\TrainerNote;

use App\Models\TrainerNote;
use App\Models\User;

/**
 * 調教師IDリストに対して、認証ユーザーの調教師メモを調教師IDごとに取得する。
 */
class LoadNotesByTrainerId
{
    /**
     * @param  list<int>  $trainerIds
     * @return array<int, array{id: int, content: string}>
     */
    public function execute(User $user, array $trainerIds): array
    {
        if ($trainerIds === []) {
            return [];
        }

        return TrainerNote::query()
            ->where('user_id', $user->id)
            ->whereIn('trainer_id', $trainerIds)
            ->get(['id', 'trainer_id', 'content'])
            ->mapWithKeys(fn (TrainerNote $note): array => [
                (int) $note->trainer_id => [
                    'id' => (int) $note->id,
                    'content' => (string) $note->content,
                ],
            ])
            ->all();
    }
}
