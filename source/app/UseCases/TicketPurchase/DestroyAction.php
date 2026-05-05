<?php

namespace App\UseCases\TicketPurchase;

use App\Models\TicketPurchase;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;

/**
 * 認証ユーザー所有の購入馬券を物理削除する。他人の馬券は AuthorizationException。
 */
class DestroyAction
{
    /**
     * @throws AuthorizationException
     */
    public function execute(TicketPurchase $purchase, User $user): void
    {
        if ((int) $purchase->user_id !== (int) $user->id) {
            throw new AuthorizationException('他のユーザーの馬券は削除できません。');
        }

        $purchase->delete();
    }
}
