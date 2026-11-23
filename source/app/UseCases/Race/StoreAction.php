<?php

namespace App\UseCases\Race;

use App\Models\Jockey;
use App\Models\Race;
use App\Models\RaceEntry;
use App\Services\RaceEntryParser;
use App\UseCases\Horse\FindOrCreateHorseAction;
use App\UseCases\Trainer\FindOrCreateTrainerAction;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * レース情報と出馬表を保存する。
 *
 * 競馬場・レース日・レース番号で既存レースを検査し、重複があれば ValidationException を投げる。
 * 出馬表テキストをパースし、未登録の馬・騎手・調教師を自動で新規登録したうえで race_entries を作成する。
 * 馬は FindOrCreateHorseAction で照合し、生年未設定の同名馬（登録馬など）がいれば生年を補完して再利用する。
 * 出馬表テキストが未指定・空白のみの場合は、確定前の登録馬を記録できるよう出走馬なしでレースのみを作成する。
 * race_name は任意項目で、未指定の場合は null として保存する。
 */
class StoreAction
{
    public function __construct(
        private readonly RaceEntryParser $parser,
        private readonly FindOrCreateTrainerAction $findOrCreateTrainerAction,
        private readonly FindOrCreateHorseAction $findOrCreateHorseAction,
    ) {}

    /**
     * @param  array{venue_id: int, race_date: string, race_number: int, paste_text?: string|null, race_name?: string|null}  $data
     *
     * @throws ValidationException
     */
    public function execute(array $data): Race
    {
        $venueId = (int) $data['venue_id'];
        $raceDate = (string) $data['race_date'];
        $raceNumber = (int) $data['race_number'];
        $pasteText = (string) ($data['paste_text'] ?? '');
        $hasPasteText = trim($pasteText) !== '';
        $raceName = $data['race_name'] ?? null;

        $exists = Race::where('venue_id', $venueId)
            ->whereDate('race_date', $raceDate)
            ->where('race_number', $raceNumber)
            ->exists();

        if ($exists) {
            throw ValidationException::withMessages([
                'paste_text' => '同じ競馬場・レース日・レース番号のレースが既に登録されています。',
            ]);
        }

        $entries = $hasPasteText
            ? $this->parser->parse($pasteText, Carbon::parse($raceDate))
            : [];

        if ($hasPasteText && $entries === []) {
            throw ValidationException::withMessages([
                'paste_text' => '出馬表の形式が認識できません。',
            ]);
        }

        return DB::transaction(function () use ($venueId, $raceDate, $raceNumber, $raceName, $entries): Race {
            $race = Race::create([
                'venue_id' => $venueId,
                'race_date' => $raceDate,
                'race_number' => $raceNumber,
                'race_name' => $raceName,
            ]);

            foreach ($entries as $entry) {
                $horse = $this->findOrCreateHorseAction->execute($entry['horse_name'], $entry['birth_year']);

                $jockey = Jockey::firstOrCreate([
                    'name' => $entry['jockey_name'],
                ]);

                $trainer = $entry['trainer_name'] !== null
                    ? $this->findOrCreateTrainerAction->execute($entry['trainer_name'], $entry['trainer_affiliation'])
                    : null;

                RaceEntry::create([
                    'race_id' => $race->id,
                    'horse_id' => $horse->id,
                    'jockey_id' => $jockey->id,
                    'trainer_id' => $trainer?->id,
                    'frame_number' => $entry['frame_number'],
                    'horse_number' => $entry['horse_number'],
                    'weight' => $entry['weight'],
                    'horse_weight' => $entry['horse_weight'],
                ]);
            }

            return $race;
        });
    }
}
