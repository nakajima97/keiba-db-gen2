<?php

namespace App\Http\Requests\HorseNote;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreHorseNoteRequest extends FormRequest
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
            'race_id' => ['nullable', 'integer', 'exists:races,id'],
            'content' => ['required', 'string', 'min:1', 'max:1000'],
        ];
    }
}
