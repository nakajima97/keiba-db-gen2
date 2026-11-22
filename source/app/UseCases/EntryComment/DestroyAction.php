<?php

namespace App\UseCases\EntryComment;

use App\Enums\CommentSpeakerType;
use App\Models\RaceEntry;
use App\Models\User;

/**
 * 調教師・騎手コメントを物理削除する。
 */
class DestroyAction
{
    public function __construct(private FindCommentAction $findCommentAction) {}

    public function execute(RaceEntry $entry, User $user, CommentSpeakerType $type, int $commentId): void
    {
        $this->findCommentAction->execute($entry, $user, $type, $commentId)->delete();
    }
}
