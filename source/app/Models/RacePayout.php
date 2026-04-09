<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RacePayout extends Model
{
    protected $fillable = [
        'race_id',
        'ticket_type_id',
        'payout_amount',
        'popularity',
    ];

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

    /** @return HasMany<RacePayoutHorse, $this> */
    public function racePayoutHorses(): HasMany
    {
        return $this->hasMany(RacePayoutHorse::class);
    }
}
