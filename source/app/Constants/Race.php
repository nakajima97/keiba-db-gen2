<?php

namespace App\Constants;

final class Race
{
    /**
     * 枠番の最大値（枠番は 1〜8）。
     */
    public const MAX_FRAME_NUMBER = 8;

    /**
     * 馬番の最大値（馬番は 1〜18）。
     */
    public const MAX_HORSE_NUMBER = 18;

    /**
     * 1開催日の最大レース番号（1〜12）。
     */
    public const MAX_RACE_NUMBER = 12;

    private function __construct() {}
}
