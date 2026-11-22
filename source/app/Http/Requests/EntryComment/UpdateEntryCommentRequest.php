<?php

namespace App\Http\Requests\EntryComment;

use App\Enums\CommentOutcome;
use App\Enums\CommentSentiment;
use App\Enums\CommentSpeakerType;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * 部分更新。送られたフィールドだけを検証・更新する（outcome は null で未入力に戻す）。
 */
class UpdateEntryCommentRequest extends FormRequest
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
            'speaker_type' => ['sometimes', 'required', Rule::enum(CommentSpeakerType::class)],
            'sentiment' => ['sometimes', 'required', Rule::enum(CommentSentiment::class)],
            'content' => ['sometimes', 'required', 'string', 'min:1', 'max:1000'],
            'outcome' => ['sometimes', 'nullable', Rule::enum(CommentOutcome::class)],
        ];
    }
}
