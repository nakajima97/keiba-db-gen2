<?php

namespace App\Http\Requests\EntryComment;

use App\Enums\CommentOutcome;
use App\Enums\CommentSentiment;
use App\Enums\CommentSpeakerType;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreEntryCommentRequest extends FormRequest
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
            'speaker_type' => ['required', Rule::enum(CommentSpeakerType::class)],
            'sentiment' => ['required', Rule::enum(CommentSentiment::class)],
            'content' => ['required', 'string', 'min:1', 'max:1000'],
            'outcome' => ['nullable', Rule::enum(CommentOutcome::class)],
        ];
    }
}
