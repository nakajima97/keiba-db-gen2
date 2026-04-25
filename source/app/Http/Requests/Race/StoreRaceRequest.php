<?php

namespace App\Http\Requests\Race;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreRaceRequest extends FormRequest
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
        return [
            'venue_id' => ['required', 'integer', 'exists:venues,id'],
            'race_date' => ['required', 'date'],
            'race_number' => ['required', 'integer', 'between:1,12'],
            'race_name' => ['nullable', 'string'],
            'paste_text' => ['required', 'string'],
        ];
    }
}
