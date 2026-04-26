<?php

namespace App\Http\Requests\RaceMark;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpsertRaceMarkRequest extends FormRequest
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
            'mark_value' => ['present', 'nullable', 'string', Rule::in(['', '◎', '○', '▲', '△', '×', '✓'])],
        ];
    }
}
