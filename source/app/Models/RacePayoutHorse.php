<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RacePayoutHorse extends Model
{
    protected $fillable = [
        'race_payout_id',
        'horse_number',
        'sort_order',
    ];

    /** @return BelongsTo<RacePayout, $this> */
    public function racePayout(): BelongsTo
    {
        return $this->belongsTo(RacePayout::class);
    }
}
