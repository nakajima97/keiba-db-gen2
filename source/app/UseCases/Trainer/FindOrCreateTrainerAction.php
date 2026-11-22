<?php

namespace App\UseCases\Trainer;

use App\Models\Trainer;
use App\Services\TrainerNameParser;

/**
 * 調教師名から調教師マスタを取得し、未登録であれば新規登録する。
 *
 * 所属が渡され、既存の調教師の所属と異なる場合（未設定・転厩）は最新の所属で更新する。
 * 所属が null の場合は既存の所属を維持する。
 */
class FindOrCreateTrainerAction
{
    public function execute(string $name, ?string $affiliation): Trainer
    {
        $trainer = Trainer::firstOrCreate(
            ['name' => $name],
            ['affiliation' => $affiliation],
        );

        if ($affiliation !== null && $trainer->affiliation !== $affiliation) {
            $trainer->update(['affiliation' => $affiliation]);
        }

        return $trainer;
    }

    /**
     * フォーム入力など「調教師名(所属)」形式を含みうる文字列から調教師を取得・登録する。
     * 空文字・null の場合は調教師なしとして null を返す。
     */
    public function executeFromText(?string $trainerText): ?Trainer
    {
        if ($trainerText === null) {
            return null;
        }

        $parsed = TrainerNameParser::parse($trainerText);
        if ($parsed === null) {
            return null;
        }

        return $this->execute($parsed['name'], $parsed['affiliation']);
    }
}
