<?php

namespace App\Enums;

use App\Models\JockeyComment;
use App\Models\TrainerComment;

/**
 * 調教師・騎手コメントの発言者種別。種別ごとに格納テーブル（モデル）が分かれる。
 *
 * @see resources/js/features/entryComment/types/index.ts と同期させること
 */
enum CommentSpeakerType: string
{
    case Trainer = 'trainer';
    case Jockey = 'jockey';

    /**
     * コメントを格納するモデルのクラス名。
     *
     * @return class-string<TrainerComment|JockeyComment>
     */
    public function modelClass(): string
    {
        return match ($this) {
            self::Trainer => TrainerComment::class,
            self::Jockey => JockeyComment::class,
        };
    }

    /**
     * 発言者を指す外部キーのカラム名（race_entries とコメントテーブルで共通）。
     */
    public function foreignKey(): string
    {
        return match ($this) {
            self::Trainer => 'trainer_id',
            self::Jockey => 'jockey_id',
        };
    }
}
