<?php

namespace App\UseCases\HorseNote;

use App\Models\HorseNote;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;

/**
 * 認証ユーザー所有の競走馬メモの本文を更新する。他人のメモは AuthorizationException。
 */
class UpdateAction
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
     * @throws AuthorizationException
     */
    public function execute(HorseNote $note, User $user, string $content): array
    {
        if ((int) $note->user_id !== (int) $user->id) {
            throw new AuthorizationException('他のユーザーのメモは変更できません。');
        }

        $note->update([
            'content' => $content,
        ]);

        $note->load(['race.venue']);

        return HorseNotePresenter::present($note);
    }
}
