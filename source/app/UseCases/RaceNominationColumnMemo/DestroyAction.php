<?php

namespace App\UseCases\RaceNominationColumnMemo;

use App\Models\RaceNominationColumnMemo;
use App\Models\RaceNominationMarkColumn;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * 登録馬に対する他人の印の印メモを削除する。
 * 印列は登録馬用の race_nomination_mark_columns。他ユーザー所有列は 403、対象メモが存在しない場合は 404。
 */
class DestroyAction
{
    /**
     * @throws AuthorizationException
     * @throws ModelNotFoundException
     */
    public function execute(RaceNominationMarkColumn $column, int $raceNominationId, User $user): void
    {
        if ((int) $column->user_id !== (int) $user->id) {
            throw new AuthorizationException('他のユーザーの印列のメモは削除できません。');
        }

        $memo = RaceNominationColumnMemo::query()
            ->where('race_nomination_mark_column_id', $column->id)
            ->where('race_nomination_id', $raceNominationId)
            ->first();

        if ($memo === null) {
            throw (new ModelNotFoundException)->setModel(RaceNominationColumnMemo::class);
        }

        $memo->delete();
    }
}
