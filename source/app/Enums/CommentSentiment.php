<?php

namespace App\Enums;

/**
 * 調教師・騎手コメントの向き（前向き / 条件付き / 弱気）。
 *
 * @see resources/js/features/entryComment/types/index.ts と同期させること
 */
enum CommentSentiment: string
{
    case Positive = 'positive';
    case Conditional = 'conditional';
    case Negative = 'negative';
}
