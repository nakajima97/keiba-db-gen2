<?php

namespace App\UseCases\RaceEntry;

use App\Models\Horse;
use App\Models\Jockey;
use App\Models\Race;
use App\Models\RaceEntry;
use Illuminate\Support\Facades\DB;

/**
 * 既存のレースに対して 1 頭の出走馬（race_entries）を個別に追加する。
 *
 * 馬名・騎手名は名前で firstOrCreate して race_entries の horse_id / jockey_id に紐付ける。
 * 馬体重は任意項目で、空文字列または null の場合は DB にも null で保存する。
 *
 * 注意: 馬の照合は name のみで行う（StoreAction は name + birth_year で照合）。
 * 個別追加フォームには生年入力欄がないため、同名馬が複数存在する場合は最初に見つかった馬に
 * 紐付く。新規作成時は birth_year=null となる。これは個別追加フォームの仕様上の制約であり、
 * 同名・別生年の取り違えリスクを許容する設計上の選択。
 */
class AddSingleAction
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
    public function execute(Race $race, array $data): void
    {
        DB::transaction(function () use ($race, $data): void {
            $horse = Horse::firstOrCreate([
                'name' => $data['horse_name'],
            ]);

            $jockey = Jockey::firstOrCreate([
                'name' => $data['jockey_name'],
            ]);

            RaceEntry::create([
                'race_id' => $race->id,
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
