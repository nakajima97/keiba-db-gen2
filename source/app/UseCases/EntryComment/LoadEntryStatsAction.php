<?php

namespace App\UseCases\EntryComment;

use App\Enums\CommentSpeakerType;
use App\Models\RaceEntry;
use App\Models\User;

/**
 * 出走馬の調教師・騎手それぞれについて、認証ユーザーの向き別の的中率を返す（openapi.yaml の EntryCommentStats）。
 */
class LoadEntryStatsAction
{
    public function __construct(private LoadStatsBySpeakerId $loadStatsBySpeakerId) {}

    /**
     * @return array{
     *     trainer_id: int|null,
     *     trainer: array<string, array{hit: int, judged: int}>|null,
     *     jockey_id: int,
     *     jockey: array<string, array{hit: int, judged: int}>,
     * }
     */
    public function execute(User $user, RaceEntry $entry): array
    {
        $trainerId = $entry->trainer_id !== null ? (int) $entry->trainer_id : null;
        $jockeyId = (int) $entry->jockey_id;

        return [
            'trainer_id' => $trainerId,
            'trainer' => $trainerId !== null
                ? $this->loadStatsBySpeakerId->execute($user, CommentSpeakerType::Trainer, [$trainerId])[$trainerId]
                : null,
            'jockey_id' => $jockeyId,
            'jockey' => $this->loadStatsBySpeakerId->execute($user, CommentSpeakerType::Jockey, [$jockeyId])[$jockeyId],
        ];
    }
}
