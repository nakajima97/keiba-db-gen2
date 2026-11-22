<?php

namespace App\UseCases\TrainerNote;

use App\Models\Trainer;
use App\Models\TrainerNote;
use App\Models\User;

/**
 * 認証ユーザーの調教師メモを物理削除する。メモが存在しない場合は何もしない（冪等）。
 */
class DestroyAction
{
    public function execute(Trainer $trainer, User $user): void
    {
        TrainerNote::query()
            ->where('user_id', $user->id)
            ->where('trainer_id', $trainer->id)
            ->delete();
    }
}
