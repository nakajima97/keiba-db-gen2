<?php

namespace App\UseCases\EntryComment;

use App\Enums\CommentSpeakerType;
use App\Models\JockeyComment;
use App\Models\RaceEntry;
use App\Models\TrainerComment;
use App\Models\User;

/**
 * 出走馬に紐づく認証ユーザーのコメントを取得する。他ユーザーのコメントや別の出走馬のコメントは見つからない扱い（404）。
 */
class FindCommentAction
{
    public function execute(RaceEntry $entry, User $user, CommentSpeakerType $type, int $commentId): TrainerComment|JockeyComment
    {
        return $type->modelClass()::query()
            ->where('user_id', $user->id)
            ->where('race_entry_id', $entry->id)
            ->findOrFail($commentId);
    }
}
