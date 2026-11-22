<?php

namespace App\UseCases\EntryComment;

use App\Enums\CommentSpeakerType;
use App\Models\RaceEntry;
use App\Models\User;
use App\Services\EntryCommentPresenter;
use Illuminate\Validation\ValidationException;

/**
 * 出走馬に対する調教師・騎手コメントを追加する。発言者は出走情報の調教師・騎手になる。
 */
class StoreAction
{
    public function __construct(
        private ResolveSpeakerIdAction $resolveSpeakerIdAction,
        private EntryCommentPresenter $presenter,
    ) {}

    /**
     * @param  array{speaker_type: string, sentiment: string, content: string, outcome?: string|null}  $validated
     * @return array<string, mixed>
     *
     * @throws ValidationException
     */
    public function execute(RaceEntry $entry, User $user, array $validated): array
    {
        $type = CommentSpeakerType::from($validated['speaker_type']);
        $speakerId = $this->resolveSpeakerIdAction->execute($entry, $type);

        $comment = $type->modelClass()::create([
            'user_id' => $user->id,
            'race_entry_id' => $entry->id,
            $type->foreignKey() => $speakerId,
            'sentiment' => $validated['sentiment'],
            'content' => $validated['content'],
            'outcome' => $validated['outcome'] ?? null,
        ]);

        return $this->presenter->present($comment->load('speaker'));
    }
}
