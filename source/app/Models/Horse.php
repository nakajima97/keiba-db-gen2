<?php

namespace App\Models;

use App\Concerns\HasNanoIdUid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Horse extends Model
{
    use HasNanoIdUid;

    protected $fillable = [
        'uid',
        'name',
        'birth_year',
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
