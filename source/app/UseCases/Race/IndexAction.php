<?php

namespace App\UseCases\Race;

use App\Models\Race;
use App\Models\Venue;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

/**
 * レース一覧画面の表示用データを返す。
 *
 * race_date 指定時はその日付のレース・競馬場を返し、未指定時は races を空配列・venues を空コレクションとする。
 * venue_id は race_date 指定時のみレース絞り込みに使われる。
 */
class IndexAction
{
    /**
     * @return array{
     *     races: Collection<int, array{
     *         uid: string,
     *         race_date: string,
     *         venue_name: string,
     *         race_number: int,
     *         race_name: string|null,
     *     }>|array<int, mixed>,
     *     venues: Collection<int, Venue>,
     *     filters: array{race_date: string|null, venue_id: int|null},
     * }
     */
    public function execute(?string $raceDate, ?int $venueId): array
    {
        $races = $raceDate ? Race::query()
            ->with('venue')
            ->where('race_date', $raceDate)
            ->when($venueId, fn ($q, $id) => $q->where('venue_id', $id))
            ->orderBy('venue_id')
            ->orderBy('race_number')
            ->get()
            ->map(fn (Race $race) => [
                'uid' => $race->uid,
                'race_date' => $race->race_date instanceof CarbonInterface
                    ? $race->race_date->format('Y-m-d')
                    : (string) $race->race_date,
                'venue_name' => $race->venue->name,
                'race_number' => $race->race_number,
                'race_name' => $race->race_name,
            ]) : [];

        $venues = $raceDate
            ? Venue::query()
                ->whereHas('races', fn ($q) => $q->where('race_date', $raceDate))
                ->orderBy('id')
                ->get(['id', 'name'])
            : collect();

        return [
            'races' => $races,
            'venues' => $venues,
            'filters' => [
                'race_date' => $raceDate,
                'venue_id' => $venueId,
            ],
        ];
    }
}
