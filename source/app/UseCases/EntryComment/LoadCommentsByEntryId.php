<?php

namespace App\UseCases\EntryComment;

use App\Enums\CommentSpeakerType;
use App\Models\User;
use App\Services\EntryCommentPresenter;

/**
 * 出走馬IDリストに対して、認証ユーザーの調教師・騎手コメントを出走馬IDごとに取得する。
 * 各出走馬のコメントは調教師・騎手を混ぜて登録が古い順に並べる。
 */
class LoadCommentsByEntryId
{
    public function __construct(private EntryCommentPresenter $presenter) {}

    /**
     * @param  list<int>  $raceEntryIds
     * @return array<int, list<array{
     *     id: int,
     *     speaker_type: string,
     *     speaker_id: int,
     *     speaker_name: string,
     *     sentiment: string,
     *     content: string,
     *     outcome: string|null,
     *     created_at: string|null,
     * }>>
     */
    public function execute(User $user, array $raceEntryIds): array
    {
        if ($raceEntryIds === []) {
            return [];
        }

        $comments = collect(CommentSpeakerType::cases())
            ->flatMap(fn (CommentSpeakerType $type) => $type->modelClass()::query()
                ->with('speaker')
                ->where('user_id', $user->id)
                ->whereIn('race_entry_id', $raceEntryIds)
                ->get())
            ->sortBy([
                fn ($a, $b) => $a->created_at <=> $b->created_at,
                fn ($a, $b) => $a->id <=> $b->id,
            ]);

        $result = [];
        foreach ($comments as $comment) {
            $result[(int) $comment->race_entry_id][] = $this->presenter->present($comment);
        }

        return $result;
    }
}
