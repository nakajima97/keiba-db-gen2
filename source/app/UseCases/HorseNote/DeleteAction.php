<?php

namespace App\UseCases\HorseNote;

use App\Models\HorseNote;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;

/**
 * 認証ユーザー所有の競走馬メモを物理削除する。他人のメモは AuthorizationException。
 */
class DeleteAction
{
    /**
     * @throws AuthorizationException
     */
    public function execute(HorseNote $note, User $user): void
    {
        if ((int) $note->user_id !== (int) $user->id) {
            throw new AuthorizationException('他のユーザーのメモは削除できません。');
        }

        $note->delete();
    }
}
