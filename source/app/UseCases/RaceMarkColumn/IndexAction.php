<?php

namespace App\UseCases\RaceMarkColumn;

use App\Models\Race;
use App\Models\RaceMarkColumn;
use App\Models\User;

/**
 * 認証ユーザー所有の印列を display_order 昇順で返す。
 * own 列が存在しない場合は自動生成する。
 */
class IndexAction
{
    public function __construct(private EnsureOwnColumnExistsAction $ensureOwnColumnExistsAction) {}

    /**
     * @return array<int, array{id: int, type: string, label: string|null, display_order: int}>
     */
    public function execute(Race $race, User $user): array
    {
        $this->ensureOwnColumnExistsAction->execute($race, $user);

        return RaceMarkColumn::query()
            ->where('race_id', $race->id)
            ->where('user_id', $user->id)
            ->orderBy('display_order')
            ->get()
            ->map(fn (RaceMarkColumn $column): array => [
                'id' => (int) $column->id,
                'type' => $column->column_type,
                'label' => $column->column_type === 'own' ? null : ($column->label ?? ''),
                'display_order' => (int) $column->display_order,
            ])
            ->all();
    }
}
