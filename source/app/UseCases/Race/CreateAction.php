<?php

namespace App\UseCases\Race;

use App\Models\Venue;
use Illuminate\Support\Collection;

/**
 * レース新規登録画面の表示用データを返す。
 *
 * 競馬場一覧と、直前に登録したレースのセッション値（次回入力時のデフォルト用）を返す。
 */
class CreateAction
{
    /**
     * @return array{
     *     venues: Collection<int, Venue>,
     *     last_venue_id: int|null,
     *     last_race_date: string|null,
     *     last_race_number: int|null,
     * }
     */
    public function execute(?int $lastVenueId, ?string $lastRaceDate, ?int $lastRaceNumber): array
    {
        return [
            'venues' => Venue::query()->orderBy('id')->get(['id', 'name']),
            'last_venue_id' => $lastVenueId,
            'last_race_date' => $lastRaceDate,
            'last_race_number' => $lastRaceNumber,
        ];
    }
}
