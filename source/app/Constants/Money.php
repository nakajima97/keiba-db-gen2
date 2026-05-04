<?php

namespace App\Constants;

final class Money
{
    /**
     * JRA が公表する払戻金額の基準購入額（100円券あたり）。
     *
     * @see resources/js/constants/money.ts JRA_PAYOUT_BASE_STAKE と同期させること
     */
    public const JRA_PAYOUT_BASE_STAKE = 100;

    private function __construct() {}
}
