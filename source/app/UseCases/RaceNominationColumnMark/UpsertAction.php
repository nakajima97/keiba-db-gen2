<?php

namespace App\UseCases\RaceNominationColumnMark;

use App\Models\RaceMarkColumn;
use App\Models\RaceNominationColumnMark;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Validation\ValidationException;

/**
 * 登録馬に対する他人の印を upsert する。mark_value が空文字列なら該当行を削除する。
 * 他ユーザー所有列は 403、own 列は 422（登録馬の自分の評価はチェック／消しで管理するため）。
 */
class UpsertAction
{
    /**
     * @return array{column_id: int, race_nomination_id: int, mark_value: string}|null
     *
     * @throws AuthorizationException
     * @throws ValidationException
     */
    public function execute(
        RaceMarkColumn $column,
        int $raceNominationId,
        User $user,
        string $markValue,
    ): ?array {
        if ((int) $column->user_id !== (int) $user->id) {
            throw new AuthorizationException('他のユーザーの印列には書き込めません。');
        }

        if ($column->column_type === 'own') {
            throw ValidationException::withMessages([
                'column_type' => '登録馬には自分の印列で印を付けられません。',
            ]);
        }

        if ($markValue === '') {
            RaceNominationColumnMark::query()
                ->where('race_mark_column_id', $column->id)
                ->where('race_nomination_id', $raceNominationId)
                ->delete();

            return null;
        }

        // 同じセルへの並行リクエストでユニーク制約違反にならないよう、DB の upsert で 1 文にまとめる。
        RaceNominationColumnMark::query()->upsert(
            [[
                'race_mark_column_id' => $column->id,
                'race_nomination_id' => $raceNominationId,
                'mark_value' => $markValue,
            ]],
            ['race_mark_column_id', 'race_nomination_id'],
            ['mark_value'],
        );

        return [
            'column_id' => (int) $column->id,
            'race_nomination_id' => $raceNominationId,
            'mark_value' => $markValue,
        ];
    }
}
