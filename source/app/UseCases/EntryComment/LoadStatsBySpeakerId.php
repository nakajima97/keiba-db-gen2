<?php

namespace App\UseCases\EntryComment;

use App\Enums\CommentOutcome;
use App\Enums\CommentSentiment;
use App\Enums\CommentSpeakerType;
use App\Models\User;

/**
 * 発言者IDリストに対して、認証ユーザーのコメントを全レース分集計した向き別の的中率を発言者IDごとに返す。
 * judged は 当たり + 外れ の件数で、判定不能・未入力は含めない。
 */
class LoadStatsBySpeakerId
{
    /**
     * @param  list<int>  $speakerIds
     * @return array<int, array<string, array{hit: int, judged: int}>>
     */
    public function execute(User $user, CommentSpeakerType $type, array $speakerIds): array
    {
        if ($speakerIds === []) {
            return [];
        }

        $foreignKey = $type->foreignKey();

        $result = [];
        foreach ($speakerIds as $speakerId) {
            $result[$speakerId] = self::emptyStats();
        }

        $rows = $type->modelClass()::query()
            ->toBase()
            ->where('user_id', $user->id)
            ->whereIn($foreignKey, $speakerIds)
            ->whereIn('outcome', [CommentOutcome::Hit->value, CommentOutcome::Miss->value])
            ->groupBy($foreignKey, 'sentiment')
            ->selectRaw("{$foreignKey} as speaker_id, sentiment, count(*) as judged, sum(case when outcome = ? then 1 else 0 end) as hit", [CommentOutcome::Hit->value])
            ->get();

        foreach ($rows as $row) {
            $result[(int) $row->speaker_id][(string) $row->sentiment] = [
                'hit' => (int) $row->hit,
                'judged' => (int) $row->judged,
            ];
        }

        return $result;
    }

    /**
     * @return array<string, array{hit: int, judged: int}>
     */
    public static function emptyStats(): array
    {
        $stats = [];
        foreach (CommentSentiment::cases() as $sentiment) {
            $stats[$sentiment->value] = ['hit' => 0, 'judged' => 0];
        }

        return $stats;
    }
}
