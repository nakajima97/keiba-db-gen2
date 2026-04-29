<?php

namespace App\UseCases\RaceMarkMemo;

use App\Models\RaceMarkColumn;
use App\Models\RaceMarkMemo;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;

/**
 * 印メモを削除する。
 * own 列は 422、他ユーザー所有列は 403、対象メモが存在しない場合は 404。
 */
class DestroyAction
{
    /**
     * @throws AuthorizationException
     * @throws ValidationException
     * @throws ModelNotFoundException
     */
    public function execute(RaceMarkColumn $column, int $raceEntryId, User $user): void
    {
        if ((int) $column->user_id !== (int) $user->id) {
            throw new AuthorizationException('他のユーザーの印列のメモは削除できません。');
        }

        if ($column->column_type === 'own') {
            throw ValidationException::withMessages([
                'column_type' => '自分の印列にはメモが存在しません。',
            ]);
        }

        $memo = RaceMarkMemo::query()
            ->where('race_mark_column_id', $column->id)
            ->where('race_entry_id', $raceEntryId)
            ->first();

        if ($memo === null) {
            throw (new ModelNotFoundException)->setModel(RaceMarkMemo::class);
        }

        $memo->delete();
    }
}
