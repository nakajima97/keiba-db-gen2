<?php

namespace App\Http\Requests\RaceEntry;

use App\Models\Race;
use App\Models\RaceEntry;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRaceEntryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        /** @var Race $race */
        $race = $this->route('race');
        /** @var RaceEntry $entry */
        $entry = $this->route('entry');

        return [
            'horse_name' => ['required', 'string'],
            'jockey_name' => ['required', 'string'],
            'frame_number' => ['required', 'integer', 'between:1,8'],
            'horse_number' => [
                'required',
                'integer',
                'between:1,18',
                Rule::unique('race_entries', 'horse_number')
                    ->where(fn ($query) => $query->where('race_id', $race->id))
                    ->ignore($entry->id),
            ],
            'weight' => ['required', 'numeric'],
            'horse_weight' => ['nullable', 'integer'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'horse_number.unique' => '同じレース内でこの馬番は既に使われています。',
        ];
    }
}
