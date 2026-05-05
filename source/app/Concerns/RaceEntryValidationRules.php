<?php

namespace App\Concerns;

use App\Constants\Race as RaceConstants;
use App\Models\Race;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\Rule;

trait RaceEntryValidationRules
{
    /**
     * 出馬登録（追加・更新）で共通するバリデーションルールを返す。
     *
     * @param  Race  $race  対象レース（horse_number の一意性スコープに使用）
     * @param  int|null  $ignoreEntryId  更新時に除外する race_entries.id
     * @return array<string, array<int, ValidationRule|array<mixed>|string>>
     */
    protected function raceEntryRules(Race $race, ?int $ignoreEntryId = null): array
    {
        $uniqueRule = Rule::unique('race_entries', 'horse_number')
            ->where(fn ($query) => $query->where('race_id', $race->id));

        if ($ignoreEntryId !== null) {
            $uniqueRule = $uniqueRule->ignore($ignoreEntryId);
        }

        return [
            'horse_name' => ['required', 'string'],
            'jockey_name' => ['required', 'string'],
            'frame_number' => ['required', 'integer', 'between:1,'.RaceConstants::MAX_FRAME_NUMBER],
            'horse_number' => [
                'required',
                'integer',
                'between:1,'.RaceConstants::MAX_HORSE_NUMBER,
                $uniqueRule,
            ],
            'weight' => ['required', 'numeric'],
            'horse_weight' => ['nullable', 'integer'],
        ];
    }
}
