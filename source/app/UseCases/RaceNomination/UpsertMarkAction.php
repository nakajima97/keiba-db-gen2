<?php

namespace App\UseCases\RaceNomination;

use App\Models\RaceNomination;
use App\Models\RaceNominationMark;
use App\Models\User;

/**
 * 登録馬に対する認証ユーザーのチェック／消しを upsert する。status が空文字列なら該当行を削除する。
 */
class UpsertMarkAction
{
    /**
     * @return array{race_nomination_id: int, status: string}|null
     */
    public function execute(RaceNomination $nomination, User $user, string $status): ?array
    {
        if ($status === '') {
            RaceNominationMark::query()
                ->where('race_nomination_id', $nomination->id)
                ->where('user_id', $user->id)
                ->delete();

            return null;
        }

        $mark = RaceNominationMark::updateOrCreate(
            [
                'race_nomination_id' => $nomination->id,
                'user_id' => $user->id,
            ],
            [
                'status' => $status,
            ],
        );

        return [
            'race_nomination_id' => (int) $mark->race_nomination_id,
            'status' => $mark->status,
        ];
    }
}
