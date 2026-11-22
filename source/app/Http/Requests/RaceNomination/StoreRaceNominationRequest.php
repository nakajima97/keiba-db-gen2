<?php

namespace App\Http\Requests\RaceNomination;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreRaceNominationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * 馬名は前後の空白（全角含む）を除去してから検証する。
     */
    protected function prepareForValidation(): void
    {
        $horseName = $this->input('horse_name');
        if (is_string($horseName)) {
            $this->merge([
                'horse_name' => preg_replace('/^[\s　]+|[\s　]+$/u', '', $horseName),
            ]);
        }
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'horse_id' => ['nullable', 'integer', 'exists:horses,id'],
            'horse_name' => ['required_without:horse_id', 'nullable', 'string', 'max:255'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'horse_name.required_without' => '馬名を入力してください。',
        ];
    }
}
