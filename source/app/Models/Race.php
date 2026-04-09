<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Race extends Model
{
    protected $fillable = [
        'uid',
        'venue_id',
        'race_date',
        'race_number',
    ];

    protected static function booted(): void
    {
        static::creating(function (Race $race): void {
            if (empty($race->uid)) {
                $race->uid = Str::random(21);
            }
        });
    }

    /** @return BelongsTo<Venue, $this> */
    public function venue(): BelongsTo
    {
        return $this->belongsTo(Venue::class);
    }

    /** @return HasMany<RacePayout, $this> */
    public function racePayouts(): HasMany
    {
        return $this->hasMany(RacePayout::class);
    }
}
