<?php

namespace App\UseCases\RaceNominationMarkColumn;

use App\Models\Race;
use App\Models\RaceNominationMarkColumn;
use App\Models\User;

/**
 * 認証ユーザー所有の登録馬用の他人の印列を display_order 昇順で返す。
 */
class IndexAction
{
    /**
     * @return list<array{id: int, label: string, display_order: int}>
     */
    public function execute(Race $race, User $user): array
    {
        return RaceNominationMarkColumn::query()
            ->where('race_id', $race->id)
            ->where('user_id', $user->id)
            ->orderBy('display_order')
            ->get()
            ->map(fn (RaceNominationMarkColumn $column): array => [
                'id' => (int) $column->id,
                'label' => (string) $column->label,
                'display_order' => (int) $column->display_order,
            ])
            ->values()
            ->all();
    }
}
