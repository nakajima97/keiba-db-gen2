<?php

namespace App\Http\Requests\RaceEntry;

use App\Concerns\RaceEntryValidationRules;
use App\Models\Race;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class AddSingleRaceEntryRequest extends FormRequest
{
    use RaceEntryValidationRules;

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

        return $this->raceEntryRules($race);
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
