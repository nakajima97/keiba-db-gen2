<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Horse extends Model
{
    protected $fillable = [
        'name',
        'birth_year',
    ];

    /** @return HasMany<RaceEntry, $this> */
    public function raceEntries(): HasMany
    {
        return $this->hasMany(RaceEntry::class);
    }
}
