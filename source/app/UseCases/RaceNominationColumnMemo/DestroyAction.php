<?php

namespace App\UseCases\RaceNominationColumnMemo;

use App\Models\RaceMarkColumn;
use App\Models\RaceNominationColumnMemo;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;

/**
 * 登録馬に対する他人の印の印メモを削除する。
 * own 列は 422、他ユーザー所有列は 403、対象メモが存在しない場合は 404。
 */
class DestroyAction
{
    /**
     * @throws AuthorizationException
     * @throws ValidationException
     * @throws ModelNotFoundException
     */
    public function execute(RaceMarkColumn $column, int $raceNominationId, User $user): void
    {
        if ((int) $column->user_id !== (int) $user->id) {
            throw new AuthorizationException('他のユーザーの印列のメモは削除できません。');
        }

        if ($column->column_type === 'own') {
            throw ValidationException::withMessages([
                'column_type' => '自分の印列にはメモが存在しません。',
            ]);
        }

        $memo = RaceNominationColumnMemo::query()
            ->where('race_mark_column_id', $column->id)
            ->where('race_nomination_id', $raceNominationId)
            ->first();

        if ($memo === null) {
            throw (new ModelNotFoundException)->setModel(RaceNominationColumnMemo::class);
        }

        $memo->delete();
    }
}
