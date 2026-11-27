<?php

namespace App\UseCases\RaceNominationMarkColumn;

use App\Models\Race;
use App\Models\RaceNominationMarkColumn;
use App\Models\User;
use Illuminate\Database\UniqueConstraintViolationException;

/**
 * 登録馬用の他人の印列を 1 列追加する。display_order はそのユーザー・レース内の最大値+1。
 */
class StoreAction
{
    /**
     * 同時追加で display_order が衝突した場合に採番し直す回数。
     */
    private const MAX_ATTEMPTS = 3;

    /**
     * @return array{id: int, label: string, display_order: int}
     *
     * @throws UniqueConstraintViolationException
     */
    public function execute(Race $race, User $user, string $label): array
    {
        for ($attempt = 1; ; $attempt++) {
            try {
                $column = $this->create($race, $user, $label);
                break;
            } catch (UniqueConstraintViolationException $e) {
                if ($attempt >= self::MAX_ATTEMPTS) {
                    throw $e;
                }
            }
        }

        return [
            'id' => (int) $column->id,
            'label' => (string) $column->label,
            'display_order' => (int) $column->display_order,
        ];
    }

    /**
     * @throws UniqueConstraintViolationException
     */
    private function create(Race $race, User $user, string $label): RaceNominationMarkColumn
    {
        $maxOrder = (int) RaceNominationMarkColumn::query()
            ->where('race_id', $race->id)
            ->where('user_id', $user->id)
            ->max('display_order');

        return RaceNominationMarkColumn::create([
            'race_id' => $race->id,
            'user_id' => $user->id,
            'label' => $label,
            'display_order' => $maxOrder + 1,
        ]);
    }
}
