<?php

namespace App\UseCases\RaceNominationColumnMark;

use App\Models\RaceNominationColumnMark;
use App\Models\RaceNominationMarkColumn;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;

/**
 * 登録馬に対する他人の印を upsert する。mark_value が空文字列なら該当行を削除する。
 * 印列は登録馬用の race_nomination_mark_columns。他ユーザー所有列は 403。
 */
class UpsertAction
{
    /**
     * @return array{column_id: int, race_nomination_id: int, mark_value: string}|null
     *
     * @throws AuthorizationException
     */
    public function execute(
        RaceNominationMarkColumn $column,
        int $raceNominationId,
        User $user,
        string $markValue,
    ): ?array {
        if ((int) $column->user_id !== (int) $user->id) {
            throw new AuthorizationException('他のユーザーの印列には書き込めません。');
        }

        if ($markValue === '') {
            RaceNominationColumnMark::query()
                ->where('race_nomination_mark_column_id', $column->id)
                ->where('race_nomination_id', $raceNominationId)
                ->delete();

            return null;
        }

        // 同じセルへの並行リクエストでユニーク制約違反にならないよう、DB の upsert で 1 文にまとめる。
        RaceNominationColumnMark::query()->upsert(
            [[
                'race_nomination_mark_column_id' => $column->id,
                'race_nomination_id' => $raceNominationId,
                'mark_value' => $markValue,
            ]],
            ['race_nomination_mark_column_id', 'race_nomination_id'],
            ['mark_value'],
        );

        return [
            'column_id' => (int) $column->id,
            'race_nomination_id' => $raceNominationId,
            'mark_value' => $markValue,
        ];
    }
}
