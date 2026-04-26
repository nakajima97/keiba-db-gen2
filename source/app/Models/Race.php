<?php

namespace App\Models;

use App\Support\NanoId;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Race extends Model
{
    protected $fillable = [
        'uid',
        'venue_id',
        'race_date',
        'race_number',
        'race_name',
    ];

    protected static function booted(): void
    {
        static::creating(function (Race $race): void {
            if (empty($race->uid)) {
                $race->uid = NanoId::generate();
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

    /** @return HasMany<RaceEntry, $this> */
    public function raceEntries(): HasMany
    {
        return $this->hasMany(RaceEntry::class);
    }

    /** @return HasMany<RaceResultHorse, $this> */
    public function raceResultHorses(): HasMany
    {
        return $this->hasMany(RaceResultHorse::class);
    }

    /** @return HasMany<RaceMarkColumn, $this> */
    public function raceMarkColumns(): HasMany
    {
        return $this->hasMany(RaceMarkColumn::class);
    }
}
