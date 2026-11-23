<?php

namespace App\Enums;

/**
 * 調教師・騎手コメントの当否（当たり / 外れ / 判定不能）。未入力は null で表す。
 *
 * @see resources/js/features/entryComment/types/index.ts と同期させること
 */
enum CommentOutcome: string
{
    case Hit = 'hit';
    case Miss = 'miss';
    case Undeterminable = 'undeterminable';
}
