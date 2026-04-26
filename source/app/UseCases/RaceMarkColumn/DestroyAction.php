<?php

namespace App\UseCases\RaceMarkColumn;

use App\Models\RaceMarkColumn;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Validation\ValidationException;

/**
 * 他人の印列を削除する。関連 race_marks は外部キー cascade で削除される。
 * own 列は削除不可（422）、他ユーザー所有列は 403。
 */
class DestroyAction
{
    /**
     * @throws AuthorizationException
     * @throws ValidationException
     */
    public function execute(RaceMarkColumn $column, User $user): void
    {
        if ((int) $column->user_id !== (int) $user->id) {
            throw new AuthorizationException('他のユーザーの印列は削除できません。');
        }

        if ($column->column_type === 'own') {
            throw ValidationException::withMessages([
                'column_type' => '自分の印列は削除できません。',
            ]);
        }

        $column->delete();
    }
}
