<?php

namespace App\UseCases\Horse;

use App\Models\Horse;

/**
 * 出馬表から取り込んだ馬名・生年で競走馬を取得し、未登録であれば新規登録する。
 *
 * 馬名・生年が一致する馬がいなくても、同名で生年未設定の馬（登録馬・出走馬個別追加で馬名のみ登録された馬）がいれば、
 * 別の馬を新規作成せずにその馬へ生年を補完して再利用する。これにより、確定前に登録馬として記録した馬と
 * 出馬表の出走馬が同じ競走馬として紐づき、チェック／消しやメモが引き継がれる。
 */
class FindOrCreateHorseAction
{
    public function execute(string $name, int $birthYear): Horse
    {
        $horse = Horse::query()
            ->where('name', $name)
            ->where('birth_year', $birthYear)
            ->first();
        if ($horse !== null) {
            return $horse;
        }

        $horseWithoutBirthYear = Horse::query()
            ->where('name', $name)
            ->whereNull('birth_year')
            ->orderBy('id')
            ->first();
        if ($horseWithoutBirthYear !== null) {
            $horseWithoutBirthYear->update(['birth_year' => $birthYear]);

            return $horseWithoutBirthYear;
        }

        return Horse::create([
            'name' => $name,
            'birth_year' => $birthYear,
        ]);
    }
}
