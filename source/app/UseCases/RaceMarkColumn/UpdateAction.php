<?php

namespace App\UseCases\RaceMarkColumn;

use App\Models\RaceMarkColumn;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Validation\ValidationException;

/**
 * 他人の印列のラベルを更新する。
 * own 列は更新不可（422）、他ユーザー所有列は 403。
 */
class UpdateAction
{
    /**
     * @return array{id: int, type: string, label: string, display_order: int}
     *
     * @throws AuthorizationException
     * @throws ValidationException
     */
    public function execute(RaceMarkColumn $column, User $user, string $label): array
    {
        if ((int) $column->user_id !== (int) $user->id) {
            throw new AuthorizationException('他のユーザーの印列は変更できません。');
        }

        if ($column->column_type === 'own') {
            throw ValidationException::withMessages([
                'column_type' => '自分の印列は更新できません。',
            ]);
        }

        $column->update([
            'label' => $label,
        ]);

        return [
            'id' => (int) $column->id,
            'type' => $column->column_type,
            'label' => $column->label ?? '',
            'display_order' => (int) $column->display_order,
        ];
    }
}
