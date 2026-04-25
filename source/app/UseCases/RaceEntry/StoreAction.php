<?php

namespace App\UseCases\RaceEntry;

use App\Models\Horse;
use App\Models\Jockey;
use App\Models\Race;
use App\Models\RaceEntry;
use App\Services\RaceEntryParser;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * 既存レースに対して JRA 出馬表テキストから出走馬情報を登録する。
 *
 * 出馬表テキストをパースし、未登録の馬・騎手を自動で新規登録したうえで race_entries を作成する。
 * パース結果が空の場合は ValidationException を投げる。
 */
class StoreAction
{
    public function __construct(
        private readonly RaceEntryParser $parser,
    ) {}

    /**
     * @throws ValidationException
     */
    public function execute(Race $race, string $pasteText): void
    {
        $raceDate = $race->race_date instanceof CarbonInterface
            ? $race->race_date
            : Carbon::parse((string) $race->race_date);

        $entries = $this->parser->parse($pasteText, $raceDate);

        if ($entries === []) {
            throw ValidationException::withMessages([
                'paste_text' => '出馬表の形式が認識できません。',
            ]);
        }

        DB::transaction(function () use ($race, $entries): void {
            foreach ($entries as $entry) {
                $horse = Horse::firstOrCreate([
                    'name' => $entry['horse_name'],
                    'birth_year' => $entry['birth_year'],
                ]);

                $jockey = Jockey::firstOrCreate([
                    'name' => $entry['jockey_name'],
                ]);

                RaceEntry::create([
                    'race_id' => $race->id,
                    'horse_id' => $horse->id,
                    'jockey_id' => $jockey->id,
                    'frame_number' => $entry['frame_number'],
                    'horse_number' => $entry['horse_number'],
                    'weight' => $entry['weight'],
                    'horse_weight' => $entry['horse_weight'],
                ]);
            }
        });
    }
}
