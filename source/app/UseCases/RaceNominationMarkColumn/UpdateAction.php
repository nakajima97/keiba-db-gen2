<?php

namespace App\UseCases\RaceNominationMarkColumn;

use App\Models\RaceNominationMarkColumn;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;

/**
 * 登録馬用の他人の印列のラベルを更新する。他ユーザー所有列は 403。
 */
class UpdateAction
{
    /**
     * @return array{id: int, label: string, display_order: int}
     *
     * @throws AuthorizationException
     */
    public function execute(RaceNominationMarkColumn $column, User $user, string $label): array
    {
        if ((int) $column->user_id !== (int) $user->id) {
            throw new AuthorizationException('他のユーザーの印列は変更できません。');
        }

        $column->update([
            'label' => $label,
        ]);

        return [
            'id' => (int) $column->id,
            'label' => (string) $column->label,
            'display_order' => (int) $column->display_order,
        ];
    }
}
