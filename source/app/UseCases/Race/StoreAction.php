<?php

namespace App\UseCases\Race;

use App\Models\Horse;
use App\Models\Jockey;
use App\Models\Race;
use App\Models\RaceEntry;
use App\Services\RaceEntryParser;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * レース情報と出馬表を保存する。
 *
 * 競馬場・レース日・レース番号で既存レースを検査し、重複があれば ValidationException を投げる。
 * 出馬表テキストをパースし、未登録の馬・騎手を自動で新規登録したうえで race_entries を作成する。
 */
class StoreAction
{
    public function __construct(
        private readonly RaceEntryParser $parser,
    ) {}

    /**
     * @param  array{venue_id: int, race_date: string, race_number: int, paste_text: string}  $data
     *
     * @throws ValidationException
     */
    public function execute(array $data): void
    {
        $venueId = (int) $data['venue_id'];
        $raceDate = (string) $data['race_date'];
        $raceNumber = (int) $data['race_number'];
        $pasteText = (string) $data['paste_text'];

        $exists = Race::where('venue_id', $venueId)
            ->whereDate('race_date', $raceDate)
            ->where('race_number', $raceNumber)
            ->exists();

        if ($exists) {
            throw ValidationException::withMessages([
                'paste_text' => '同じ競馬場・レース日・レース番号のレースが既に登録されています。',
            ]);
        }

        $entries = $this->parser->parse($pasteText, Carbon::parse($raceDate));

        if ($entries === []) {
            throw ValidationException::withMessages([
                'paste_text' => '出馬表の形式が認識できません。',
            ]);
        }

        DB::transaction(function () use ($venueId, $raceDate, $raceNumber, $entries): void {
            $race = Race::create([
                'venue_id' => $venueId,
                'race_date' => $raceDate,
                'race_number' => $raceNumber,
            ]);

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
