<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TicketPurchase extends Model
{
    protected $fillable = [
        'user_id',
        'race_id',
        'ticket_type_id',
        'buy_type_id',
        'selections',
        'amount',
        'payout_amount',
    ];

    protected function casts(): array
    {
        return [
            'selections' => 'array',
        ];
    }

    /** @return BelongsTo<Race, $this> */
    public function race(): BelongsTo
    {
        return $this->belongsTo(Race::class);
    }

    /** @return BelongsTo<TicketType, $this> */
    public function ticketType(): BelongsTo
    {
        return $this->belongsTo(TicketType::class);
    }

    /** @return BelongsTo<BuyType, $this> */
    public function buyType(): BelongsTo
    {
        return $this->belongsTo(BuyType::class);
    }
}
