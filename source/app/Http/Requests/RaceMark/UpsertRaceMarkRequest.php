<?php

namespace App\Http\Requests\RaceMark;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpsertRaceMarkRequest extends FormRequest
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
            'mark_value' => ['present', 'string', Rule::in(['', '◎', '○', '▲', '△', '×', '✓'])],
        ];
    }
}
