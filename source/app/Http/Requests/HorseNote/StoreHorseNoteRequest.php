<?php

namespace App\Http\Requests\HorseNote;

use App\Models\Horse;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;

class StoreHorseNoteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string|\Closure>
     */
    public function rules(): array
    {
        return [
            'race_id' => [
                'nullable',
                'integer',
                'exists:races,id',
                $this->raceMatchesHorseRule(),
            ],
            'content' => ['required', 'string', 'min:1', 'max:1000'],
        ];
    }

    /**
     * race_id が指定された場合、対象の競走馬がそのレースに出走（出走表 or 結果）していることを保証する。
     */
    private function raceMatchesHorseRule(): \Closure
    {
        return function (string $attribute, mixed $value, \Closure $fail): void {
            $horse = $this->route('horse');
            if (! $horse instanceof Horse) {
                return;
            }

            $hasEntry = DB::table('race_entries')
                ->where('race_id', $value)
                ->where('horse_id', $horse->id)
                ->exists();

            if ($hasEntry) {
                return;
            }

            $hasResult = DB::table('race_result_horses')
                ->where('race_id', $value)
                ->where('horse_id', $horse->id)
                ->exists();

            if (! $hasResult) {
                $fail('指定したレースにこの競走馬の出走情報がありません。');
            }
        };
    }
}
