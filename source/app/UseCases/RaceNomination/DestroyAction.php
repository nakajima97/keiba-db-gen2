<?php

namespace App\UseCases\RaceNomination;

use App\Models\RaceNomination;

/**
 * 登録馬を削除する。チェック／消しは外部キーの cascade で削除され、競走馬メモ（horse_notes）は残る。
 */
class DestroyAction
{
    public function execute(RaceNomination $nomination): void
    {
        $nomination->delete();
    }
}
