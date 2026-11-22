<?php

namespace App\Models;

use Database\Factories\TrainerFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Trainer extends Model
{
    /** @use HasFactory<TrainerFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'affiliation',
    ];

    /** @return HasMany<RaceEntry, $this> */
    public function raceEntries(): HasMany
    {
        return $this->hasMany(RaceEntry::class);
    }

    /** @return HasMany<TrainerNote, $this> */
    public function notes(): HasMany
    {
        return $this->hasMany(TrainerNote::class);
    }
}
