<?php

namespace App\UseCases\TicketPurchase;

use App\Models\Race;
use App\Models\TicketPurchase;
use App\Models\Venue;
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

        $raceId = null;
        if (! empty($data['race_date']) && ! empty($data['race_number'])) {
            $venueId = Venue::where('name', $data['venue'])->value('id');

            if ($venueId) {
                $raceId = Race::firstOrCreate(
                    [
                        'venue_id' => $venueId,
                        'race_date' => $data['race_date'],
                        'race_number' => $data['race_number'],
                    ]
                )->id;
            }
        }

        return TicketPurchase::create([
            'user_id' => $userId,
            'race_id' => $raceId,
            'ticket_type_id' => $ticketTypeId,
            'buy_type_id' => $buyTypeId,
            'selections' => $data['selections'],
            'amount' => $data['amount'] ?? null,
        ]);
    }
}
