<?php

namespace App\UseCases\RaceNomination;

use App\Models\RaceNomination;

/**
 * RaceNomination をレース詳細画面・API レスポンス用の配列に整形する。
 * 呼び出し側で horse リレーションを読み込んでおくこと。
 */
class RaceNominationPresenter
{
    /**
     * @param  array{id: int, content: string, source: string}|null  $note
     * @return array{
     *     id: int,
     *     horse_id: int,
     *     horse_uid: string,
     *     horse_name: string,
     *     status: string|null,
     *     note: array{id: int, content: string, source: string}|null,
     * }
     */
    public static function present(RaceNomination $nomination, ?string $status, ?array $note): array
    {
        return [
            'id' => (int) $nomination->id,
            'horse_id' => (int) $nomination->horse->id,
            'horse_uid' => (string) $nomination->horse->uid,
            'horse_name' => $nomination->horse->name,
            'status' => $status,
            'note' => $note,
        ];
    }
}
