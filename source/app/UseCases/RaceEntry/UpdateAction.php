<?php

namespace App\UseCases\RaceEntry;

use App\Models\Horse;
use App\Models\Jockey;
use App\Models\RaceEntry;
use Illuminate\Support\Facades\DB;

/**
 * 既存の出走馬レコード（race_entries）を編集する。
 *
 * 馬名・騎手名は名前で firstOrCreate して race_entries の horse_id / jockey_id を差し替える。
 * 馬体重は任意項目で、null の場合は DB にも null で保存する。
 *
 * 注意: 馬の照合は name のみで行う（StoreAction は name + birth_year で照合）。
 * 編集フォームには生年入力欄がないため、同名馬が複数存在する場合は最初に見つかった馬に
 * 紐付く。新規作成時は birth_year=null となる。これは編集用フォームの仕様上の制約であり、
 * 同名・別生年の取り違えリスクを許容する設計上の選択。
 */
class UpdateAction
{
    /**
     * @param  array{
     *     horse_name: string,
     *     jockey_name: string,
     *     frame_number: int,
     *     horse_number: int,
     *     weight: float|string,
     *     horse_weight: int|string|null,
     * }  $data
     */
    public function execute(RaceEntry $entry, array $data): void
    {
        DB::transaction(function () use ($entry, $data): void {
            $horse = Horse::firstOrCreate([
                'name' => $data['horse_name'],
            ]);

            $jockey = Jockey::firstOrCreate([
                'name' => $data['jockey_name'],
            ]);

            $entry->update([
                'horse_id' => $horse->id,
                'jockey_id' => $jockey->id,
                'frame_number' => $data['frame_number'],
                'horse_number' => $data['horse_number'],
                'weight' => $data['weight'],
                'horse_weight' => $data['horse_weight'] !== null && $data['horse_weight'] !== ''
                    ? (int) $data['horse_weight']
                    : null,
            ]);
        });
    }
}
