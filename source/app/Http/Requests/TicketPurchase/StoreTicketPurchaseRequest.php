<?php

namespace App\Http\Requests\TicketPurchase;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreTicketPurchaseRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'venue' => ['required', 'string', 'exists:venues,name'],
            'race_date' => ['nullable', 'date'],
            'race_number' => ['nullable', 'integer', 'min:1', 'max:12'],
            'ticket_type' => ['required', 'string', 'exists:ticket_types,name'],
            'buy_type' => ['required', 'string', 'exists:buy_types,name'],
            'selections' => ['required', 'array'],
            'amount' => ['nullable', 'integer', 'min:100'],
        ];
    }
}
