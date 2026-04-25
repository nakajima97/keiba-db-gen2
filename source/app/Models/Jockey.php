<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Jockey extends Model
{
    protected $fillable = [
        'name',
    ];

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
}
