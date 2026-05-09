<?php

namespace App\Models;

use App\Support\NanoId;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RaceEntry extends Model
{
    protected $fillable = [
        'uid',
        'race_id',
        'horse_id',
        'jockey_id',
        'frame_number',
        'horse_number',
        'weight',
        'horse_weight',
    ];

    protected static function booted(): void
    {
        static::creating(function (RaceEntry $entry): void {
            if (empty($entry->uid)) {
                $entry->uid = NanoId::generate();
            }
        });
    }

    /** @return BelongsTo<Race, $this> */
    public function race(): BelongsTo
    {
        return $this->belongsTo(Race::class);
    }

    /** @return BelongsTo<Horse, $this> */
    public function horse(): BelongsTo
    {
        return $this->belongsTo(Horse::class);
    }

    /** @return BelongsTo<Jockey, $this> */
    public function jockey(): BelongsTo
    {
        return $this->belongsTo(Jockey::class);
    }
}
