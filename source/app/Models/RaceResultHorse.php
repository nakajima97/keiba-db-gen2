<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RaceResultHorse extends Model
{
    protected $fillable = [
        'race_id',
        'horse_id',
        'jockey_id',
        'finishing_order',
        'frame_number',
        'horse_number',
        'horse_name',
        'sex_age',
        'weight',
        'jockey_name',
        'race_time',
        'time_difference',
        'corner_order',
        'estimated_pace',
        'horse_weight',
        'horse_weight_change',
        'trainer_name',
        'popularity',
    ];

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
