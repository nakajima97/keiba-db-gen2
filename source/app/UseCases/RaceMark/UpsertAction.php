<?php

namespace App\UseCases\RaceMark;

use App\Models\RaceMark;
use App\Models\RaceMarkColumn;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;

/**
 * 印を upsert する。mark_value が空文字列なら該当行を削除する。
 * 他ユーザー所有列に対する操作は 403。
 */
class UpsertAction
{
    /**
     * @return array{column_id: int, race_entry_id: int, mark_value: string}|null
     *
     * @throws AuthorizationException
     */
    public function execute(
        RaceMarkColumn $column,
        int $raceEntryId,
        User $user,
        string $markValue,
    ): ?array {
        if ((int) $column->user_id !== (int) $user->id) {
            throw new AuthorizationException('他のユーザーの印列には書き込めません。');
        }

        if ($markValue === '') {
            RaceMark::query()
                ->where('race_mark_column_id', $column->id)
                ->where('race_entry_id', $raceEntryId)
                ->delete();

            return null;
        }

        $mark = RaceMark::updateOrCreate(
            [
                'race_mark_column_id' => $column->id,
                'race_entry_id' => $raceEntryId,
            ],
            [
                'mark_value' => $markValue,
            ],
        );

        return [
            'column_id' => (int) $mark->race_mark_column_id,
            'race_entry_id' => (int) $mark->race_entry_id,
            'mark_value' => $mark->mark_value,
        ];
    }
}
