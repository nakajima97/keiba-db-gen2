<?php

namespace App\UseCases\RaceMarkColumn;

use App\Models\Race;
use App\Models\RaceMarkColumn;
use App\Models\User;

/**
 * 他人の印列を 1 列追加する。display_order はそのユーザー・レース内の最大値+1。
 */
class StoreAction
{
    /**
     * @return array{id: int, type: string, label: string, display_order: int}
     */
    public function execute(Race $race, User $user, string $label): array
    {
        $maxOrder = (int) RaceMarkColumn::query()
            ->where('race_id', $race->id)
            ->where('user_id', $user->id)
            ->max('display_order');

        $column = RaceMarkColumn::create([
            'race_id' => $race->id,
            'user_id' => $user->id,
            'column_type' => 'other',
            'label' => $label,
            'display_order' => $maxOrder + 1,
        ]);

        return [
            'id' => (int) $column->id,
            'type' => $column->column_type,
            'label' => $column->label ?? '',
            'display_order' => (int) $column->display_order,
        ];
    }
}
