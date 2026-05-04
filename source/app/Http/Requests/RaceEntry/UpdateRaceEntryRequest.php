<?php

namespace App\Http\Requests\RaceEntry;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

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
        return [
            'horse_name' => ['required', 'string'],
            'jockey_name' => ['required', 'string'],
            'frame_number' => ['required', 'integer', 'between:1,8'],
            'horse_number' => ['required', 'integer', 'between:1,18'],
            'weight' => ['required', 'numeric'],
            'horse_weight' => ['nullable', 'integer'],
        ];
    }
}
