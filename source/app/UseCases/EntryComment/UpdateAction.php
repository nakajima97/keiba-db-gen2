<?php

namespace App\UseCases\EntryComment;

use App\Enums\CommentSpeakerType;
use App\Models\RaceEntry;
use App\Models\User;
use App\Services\EntryCommentPresenter;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * 調教師・騎手コメントを部分更新する。送られたフィールドだけを更新する。
 * 発言者種別を変更した場合は格納テーブルが変わるため、反対側のテーブルに作り直す（created_at は引き継ぐ）。
 */
class UpdateAction
{
    public function __construct(
        private FindCommentAction $findCommentAction,
        private ResolveSpeakerIdAction $resolveSpeakerIdAction,
        private EntryCommentPresenter $presenter,
    ) {}

    /**
     * @param  array{speaker_type?: string, sentiment?: string, content?: string, outcome?: string|null}  $validated
     * @return array<string, mixed>
     *
     * @throws ValidationException
     */
    public function execute(RaceEntry $entry, User $user, CommentSpeakerType $currentType, int $commentId, array $validated): array
    {
        $comment = $this->findCommentAction->execute($entry, $user, $currentType, $commentId);
        $attributes = Arr::only($validated, ['sentiment', 'content', 'outcome']);
        $newType = isset($validated['speaker_type'])
            ? CommentSpeakerType::from($validated['speaker_type'])
            : $currentType;

        if ($newType === $currentType) {
            $comment->update($attributes);

            return $this->presenter->present($comment->load('speaker'));
        }

        $speakerId = $this->resolveSpeakerIdAction->execute($entry, $newType);

        $moved = DB::transaction(function () use ($comment, $attributes, $newType, $speakerId, $user, $entry) {
            $moved = $newType->modelClass()::create([
                'user_id' => $user->id,
                'race_entry_id' => $entry->id,
                $newType->foreignKey() => $speakerId,
                'sentiment' => $attributes['sentiment'] ?? $comment->sentiment,
                'content' => $attributes['content'] ?? $comment->content,
                'outcome' => array_key_exists('outcome', $attributes) ? $attributes['outcome'] : $comment->outcome,
                'created_at' => $comment->created_at,
            ]);
            $comment->delete();

            return $moved;
        });

        return $this->presenter->present($moved->load('speaker'));
    }
}
