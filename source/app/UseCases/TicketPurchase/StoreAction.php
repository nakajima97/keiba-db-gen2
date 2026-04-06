<?php

namespace App\UseCases\TicketPurchase;

use App\Models\TicketPurchase;
use Illuminate\Support\Facades\DB;

class StoreAction
{
    public function execute(array $data, int $userId): TicketPurchase
    {
        $ticketTypeId = DB::table('ticket_types')
            ->where('name', $data['ticket_type'])
            ->value('id');

        $buyTypeId = DB::table('buy_types')
            ->where('name', $data['buy_type'])
            ->value('id');

        return TicketPurchase::create([
            'user_id' => $userId,
            'race_id' => null,
            'ticket_type_id' => $ticketTypeId,
            'buy_type_id' => $buyTypeId,
            'selections' => $data['selections'],
            'amount' => $data['amount'] ?? null,
        ]);
    }
}
