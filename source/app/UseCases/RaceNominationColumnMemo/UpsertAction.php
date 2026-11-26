<?php

namespace App\UseCases\RaceNominationColumnMemo;

use App\Models\RaceNominationColumnMemo;
use App\Models\RaceNominationMarkColumn;
use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;

/**
 * 登録馬に対する他人の印の印メモを upsert する。
 * 印列は登録馬用の race_nomination_mark_columns。他ユーザー所有列は 403。
 */
class UpsertAction
{
    /**
     * @return array{
     *     created: bool,
     *     memo: array{
     *         id: int,
     *         race_nomination_mark_column_id: int,
     *         race_nomination_id: int,
     *         content: string,
     *         created_at: string|null,
     *         updated_at: string|null,
     *     }
     * }
     *
     * @throws AuthorizationException
     */
    public function execute(
        RaceNominationMarkColumn $column,
        int $raceNominationId,
        User $user,
        string $content,
    ): array {
        if ((int) $column->user_id !== (int) $user->id) {
            throw new AuthorizationException('他のユーザーの印列にはメモを書き込めません。');
        }

        try {
            return $this->upsert($column, $raceNominationId, $content);
        } catch (UniqueConstraintViolationException) {
            // 同じセルの新規作成が並行した場合、先に作成された行を更新し直す（デッドロックは DB::transaction の再試行で吸収する）。
            return $this->upsert($column, $raceNominationId, $content);
        }
    }

    /**
     * @return array{
     *     created: bool,
     *     memo: array{
     *         id: int,
     *         race_nomination_mark_column_id: int,
     *         race_nomination_id: int,
     *         content: string,
     *         created_at: string|null,
     *         updated_at: string|null,
     *     }
     * }
     *
     * @throws UniqueConstraintViolationException
     */
    private function upsert(RaceNominationMarkColumn $column, int $raceNominationId, string $content): array
    {
        return DB::transaction(function () use ($column, $raceNominationId, $content): array {
            $existing = RaceNominationColumnMemo::query()
                ->where('race_nomination_mark_column_id', $column->id)
                ->where('race_nomination_id', $raceNominationId)
                ->lockForUpdate()
                ->first();

            if ($existing !== null) {
                $existing->update(['content' => $content]);

                return [
                    'created' => false,
                    'memo' => $this->present($existing),
                ];
            }

            $memo = RaceNominationColumnMemo::create([
                'race_nomination_mark_column_id' => $column->id,
                'race_nomination_id' => $raceNominationId,
                'content' => $content,
            ]);

            return [
                'created' => true,
                'memo' => $this->present($memo),
            ];
        }, attempts: 2);
    }

    /**
     * @return array{
     *     id: int,
     *     race_nomination_mark_column_id: int,
     *     race_nomination_id: int,
     *     content: string,
     *     created_at: string|null,
     *     updated_at: string|null,
     * }
     */
    private function present(RaceNominationColumnMemo $memo): array
    {
        return [
            'id' => (int) $memo->id,
            'race_nomination_mark_column_id' => (int) $memo->race_nomination_mark_column_id,
            'race_nomination_id' => (int) $memo->race_nomination_id,
            'content' => (string) $memo->content,
            'created_at' => $memo->created_at instanceof CarbonInterface
                ? $memo->created_at->toIso8601String()
                : null,
            'updated_at' => $memo->updated_at instanceof CarbonInterface
                ? $memo->updated_at->toIso8601String()
                : null,
        ];
    }
}
