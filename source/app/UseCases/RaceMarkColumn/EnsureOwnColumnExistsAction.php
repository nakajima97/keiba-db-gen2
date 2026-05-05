<?php

namespace App\UseCases\RaceMarkColumn;

use App\Models\Race;
use App\Models\RaceMarkColumn;
use App\Models\User;
use Illuminate\Database\UniqueConstraintViolationException;

/**
 * 指定レース・ユーザーに対する RaceMarkColumn の "own" 列が存在しない場合に自動生成する。
 */
class EnsureOwnColumnExistsAction
{
    public function execute(Race $race, User $user): void
    {
        try {
            RaceMarkColumn::firstOrCreate(
                [
                    'race_id' => $race->id,
                    'user_id' => $user->id,
                    'column_type' => 'own',
                ],
                [
                    'label' => null,
                    'display_order' => 0,
                ],
            );
        } catch (UniqueConstraintViolationException) {
            // 別リクエストが先に作成済み。次のクエリで読めるので無視。
        }
    }
}
