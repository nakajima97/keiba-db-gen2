<?php

namespace App\UseCases\RaceMarkMemo;

use App\Models\RaceMarkColumn;
use App\Models\RaceMarkMemo;
use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Validation\ValidationException;

/**
 * 印メモを upsert する。
 * own 列は 422、他ユーザー所有列は 403。
 */
class UpsertAction
{
    /**
     * @return array{
     *     created: bool,
     *     memo: array{
     *         id: int,
     *         race_mark_column_id: int,
     *         race_entry_id: int,
     *         content: string,
     *         created_at: string|null,
     *         updated_at: string|null,
     *     }
     * }
     *
     * @throws AuthorizationException
     * @throws ValidationException
     */
    public function execute(
        RaceMarkColumn $column,
        int $raceEntryId,
        User $user,
        string $content,
    ): array {
        if ((int) $column->user_id !== (int) $user->id) {
            throw new AuthorizationException('他のユーザーの印列にはメモを書き込めません。');
        }

        if ($column->column_type === 'own') {
            throw ValidationException::withMessages([
                'column_type' => '自分の印列にはメモを作成できません。',
            ]);
        }

        $existing = RaceMarkMemo::query()
            ->where('race_mark_column_id', $column->id)
            ->where('race_entry_id', $raceEntryId)
            ->first();

        if ($existing !== null) {
            $existing->update(['content' => $content]);

            return [
                'created' => false,
                'memo' => $this->present($existing),
            ];
        }

        $memo = RaceMarkMemo::create([
            'race_mark_column_id' => $column->id,
            'race_entry_id' => $raceEntryId,
            'content' => $content,
        ]);

        return [
            'created' => true,
            'memo' => $this->present($memo),
        ];
    }

    /**
     * @return array{
     *     id: int,
     *     race_mark_column_id: int,
     *     race_entry_id: int,
     *     content: string,
     *     created_at: string|null,
     *     updated_at: string|null,
     * }
     */
    private function present(RaceMarkMemo $memo): array
    {
        return [
            'id' => (int) $memo->id,
            'race_mark_column_id' => (int) $memo->race_mark_column_id,
            'race_entry_id' => (int) $memo->race_entry_id,
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
