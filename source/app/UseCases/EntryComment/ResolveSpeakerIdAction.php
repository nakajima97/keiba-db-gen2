<?php

namespace App\UseCases\EntryComment;

use App\Enums\CommentSpeakerType;
use App\Models\RaceEntry;
use Illuminate\Validation\ValidationException;

/**
 * 発言者種別に対応する出走情報の調教師・騎手IDを返す。
 * 調教師が未登録の出走馬で調教師を指定した場合はバリデーションエラーにする。
 */
class ResolveSpeakerIdAction
{
    /**
     * @throws ValidationException
     */
    public function execute(RaceEntry $entry, CommentSpeakerType $type): int
    {
        $speakerId = $entry->getAttribute($type->foreignKey());

        if ($speakerId === null) {
            $label = $type === CommentSpeakerType::Trainer ? '調教師' : '騎手';

            throw ValidationException::withMessages([
                'speaker_type' => "{$label}が未登録の出走馬には{$label}コメントを登録できません。",
            ]);
        }

        return (int) $speakerId;
    }
}
