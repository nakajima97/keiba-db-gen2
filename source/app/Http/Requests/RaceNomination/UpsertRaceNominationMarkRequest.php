<?php

namespace App\Http\Requests\RaceNomination;

use App\Models\RaceNominationMark;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpsertRaceNominationMarkRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'status' => ['present', 'nullable', 'string', Rule::in(['', ...RaceNominationMark::STATUSES])],
        ];
    }
}
