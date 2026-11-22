<?php

namespace App\UseCases\RaceNomination;

use App\Models\Race;
use App\Models\RaceNomination;
use App\Models\RaceNominationMark;
use App\Models\User;
use App\UseCases\HorseNote\LoadNotesByHorseId;

/**
 * レースの登録馬を登録順に取得し、認証ユーザーのチェック／消しと競走馬メモを付与して返す。
 */
class LoadNominationsAction
{
    public function __construct(
        private LoadNotesByHorseId $loadNotesByHorseId,
    ) {}

    /**
     * @return list<array{
     *     id: int,
     *     horse_id: int,
     *     horse_uid: string,
     *     horse_name: string,
     *     status: string|null,
     *     note: array{id: int, content: string, source: string}|null,
     * }>
     */
    public function execute(Race $race, User $user): array
    {
        $nominations = RaceNomination::query()
            ->where('race_id', $race->id)
            ->with('horse')
            ->orderBy('id')
            ->get();

        if ($nominations->isEmpty()) {
            return [];
        }

        $statusByNominationId = RaceNominationMark::query()
            ->whereIn('race_nomination_id', $nominations->pluck('id'))
            ->where('user_id', $user->id)
            ->pluck('status', 'race_nomination_id')
            ->all();

        $horseIds = $nominations->map(fn (RaceNomination $nomination): int => (int) $nomination->horse_id)
            ->unique()
            ->values()
            ->all();
        $notesByHorseId = $this->loadNotesByHorseId->execute($user, $horseIds, (int) $race->id);

        return $nominations->map(fn (RaceNomination $nomination): array => RaceNominationPresenter::present(
            $nomination,
            $statusByNominationId[$nomination->id] ?? null,
            $notesByHorseId[(int) $nomination->horse_id] ?? null,
        ))->values()->all();
    }
}
