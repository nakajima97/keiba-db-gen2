<?php

namespace App\UseCases\HorseNote;

use App\Models\Horse;
use App\Models\HorseNote;
use App\Models\User;

/**
 * 認証ユーザー所有のメモを指定競走馬について updated_at 降順で返す。
 * レース紐づきありのメモは race フィールドにレース情報を含む。
 */
class IndexAction
{
    /**
     * @return list<array{
     *     id: int,
     *     horse_id: int,
     *     race_id: int|null,
     *     race: array{uid: string, race_date: string, venue_name: string, race_number: int, race_name: string|null}|null,
     *     content: string,
     *     created_at: string,
     *     updated_at: string,
     * }>
     */
    public function execute(Horse $horse, User $user): array
    {
        return HorseNote::query()
            ->with(['race.venue'])
            ->where('user_id', $user->id)
            ->where('horse_id', $horse->id)
            ->orderBy('updated_at', 'desc')
            ->get()
            ->map(fn (HorseNote $note): array => HorseNotePresenter::present($note))
            ->all();
    }
}
