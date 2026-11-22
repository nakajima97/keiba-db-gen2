<?php

namespace App\UseCases\RaceNomination;

use App\Models\Horse;
use App\Models\Race;
use App\Models\RaceNomination;
use App\Models\User;
use App\UseCases\HorseNote\LoadNotesByHorseId;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * レースに登録馬を 1 頭追加する。
 *
 * horse_id が指定されればその馬を、指定がなければ馬名で照合し、存在しなければ生年なしで新規作成する。
 * 馬名照合は出走馬の個別追加（RaceEntry\AddSingleAction）と同じく name のみで行うため、
 * 同名馬が複数存在する場合は最初に見つかった馬に紐付く。
 */
class StoreAction
{
    public function __construct(
        private LoadNotesByHorseId $loadNotesByHorseId,
    ) {}

    /**
     * @return array{
     *     id: int,
     *     horse_id: int,
     *     horse_uid: string,
     *     horse_name: string,
     *     status: string|null,
     *     note: array{id: int, content: string, source: string}|null,
     * }
     *
     * @throws ValidationException
     */
    public function execute(Race $race, User $user, ?int $horseId, ?string $horseName): array
    {
        $nomination = DB::transaction(function () use ($race, $horseId, $horseName): RaceNomination {
            $horse = $horseId !== null
                ? Horse::query()->findOrFail($horseId)
                : Horse::firstOrCreate(['name' => (string) $horseName]);

            $alreadyNominated = RaceNomination::query()
                ->where('race_id', $race->id)
                ->where('horse_id', $horse->id)
                ->exists();
            if ($alreadyNominated) {
                throw $this->duplicateException();
            }

            try {
                return RaceNomination::create([
                    'race_id' => $race->id,
                    'horse_id' => $horse->id,
                ]);
            } catch (UniqueConstraintViolationException) {
                // exists() と create() の間に他リクエストが先に登録した場合のフォールバック
                throw $this->duplicateException();
            }
        });

        $nomination->load('horse');
        $notesByHorseId = $this->loadNotesByHorseId->execute($user, [(int) $nomination->horse_id], (int) $race->id);

        return RaceNominationPresenter::present(
            $nomination,
            null,
            $notesByHorseId[(int) $nomination->horse_id] ?? null,
        );
    }

    private function duplicateException(): ValidationException
    {
        return ValidationException::withMessages([
            'horse_name' => 'この馬は既に登録馬に追加されています。',
        ]);
    }
}
