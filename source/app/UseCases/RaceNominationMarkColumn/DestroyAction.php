<?php

namespace App\UseCases\RaceNominationMarkColumn;

use App\Models\RaceNominationMarkColumn;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;

/**
 * 登録馬用の他人の印列を削除する。関連する登録馬の他人の印・印メモは外部キー cascade で削除される。
 * 他ユーザー所有列は 403。
 */
class DestroyAction
{
    /**
     * @throws AuthorizationException
     */
    public function execute(RaceNominationMarkColumn $column, User $user): void
    {
        if ((int) $column->user_id !== (int) $user->id) {
            throw new AuthorizationException('他のユーザーの印列は削除できません。');
        }

        $column->delete();
    }
}
