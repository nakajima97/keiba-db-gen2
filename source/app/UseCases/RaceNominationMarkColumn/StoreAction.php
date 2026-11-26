<?php

namespace App\UseCases\RaceNominationMarkColumn;

use App\Models\Race;
use App\Models\RaceNominationMarkColumn;
use App\Models\User;

/**
 * 登録馬用の他人の印列を 1 列追加する。display_order はそのユーザー・レース内の最大値+1。
 */
class StoreAction
{
    /**
     * @return array{id: int, label: string, display_order: int}
     */
    public function execute(Race $race, User $user, string $label): array
    {
        $maxOrder = (int) RaceNominationMarkColumn::query()
            ->where('race_id', $race->id)
            ->where('user_id', $user->id)
            ->max('display_order');

        $column = RaceNominationMarkColumn::create([
            'race_id' => $race->id,
            'user_id' => $user->id,
            'label' => $label,
            'display_order' => $maxOrder + 1,
        ]);

        return [
            'id' => (int) $column->id,
            'label' => (string) $column->label,
            'display_order' => (int) $column->display_order,
        ];
    }
}
