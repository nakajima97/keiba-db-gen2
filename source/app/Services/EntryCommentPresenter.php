<?php

namespace App\Services;

use App\Enums\CommentSpeakerType;
use App\Models\JockeyComment;
use App\Models\TrainerComment;
use Carbon\CarbonInterface;

/**
 * 調教師・騎手コメントを API / Inertia props 用の配列（openapi.yaml の EntryComment）に変換する。
 */
class EntryCommentPresenter
{
    /**
     * speaker リレーションを読み込み済みのコメントを渡すこと。
     *
     * @return array{
     *     id: int,
     *     speaker_type: string,
     *     speaker_id: int,
     *     speaker_name: string,
     *     sentiment: string,
     *     content: string,
     *     outcome: string|null,
     *     created_at: string|null,
     * }
     */
    public function present(TrainerComment|JockeyComment $comment): array
    {
        $type = $comment instanceof TrainerComment ? CommentSpeakerType::Trainer : CommentSpeakerType::Jockey;

        return [
            'id' => (int) $comment->id,
            'speaker_type' => $type->value,
            'speaker_id' => (int) $comment->getAttribute($type->foreignKey()),
            'speaker_name' => (string) $comment->speaker->name,
            'sentiment' => $comment->sentiment->value,
            'content' => (string) $comment->content,
            'outcome' => $comment->outcome?->value,
            'created_at' => $comment->created_at instanceof CarbonInterface
                ? $comment->created_at->toIso8601String()
                : null,
        ];
    }
}
