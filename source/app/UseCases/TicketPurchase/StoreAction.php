<?php

namespace App\UseCases\TicketPurchase;

use App\Models\BuyType;
use App\Models\Race;
use App\Models\RacePayout;
use App\Models\TicketPurchase;
use App\Models\TicketType;
use App\Models\Venue;

class StoreAction
{
    public function __construct(
        private readonly CalculatePayoutAmountAction $calculatePayoutAmountAction,
    ) {}

    public function execute(array $data, int $userId): TicketPurchase
    {
        $ticketTypeId = TicketType::where('name', $data['ticket_type'])->value('id');

        $buyTypeId = BuyType::where('name', $data['buy_type'])->value('id');

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

        $ticketPurchase = TicketPurchase::create([
            'user_id' => $userId,
            'race_id' => $raceId,
            'ticket_type_id' => $ticketTypeId,
            'buy_type_id' => $buyTypeId,
            'selections' => $data['selections'],
            'unit_stake' => $data['unit_stake'] ?? null,
        ]);

        if ($raceId !== null && RacePayout::where('race_id', $raceId)->exists()) {
            $payoutAmount = $this->calculatePayoutAmountAction->execute($ticketPurchase);
            if ($payoutAmount !== null) {
                $ticketPurchase->payout_amount = $payoutAmount;
                $ticketPurchase->save();
            }
        }

        return $ticketPurchase;
    }
}
