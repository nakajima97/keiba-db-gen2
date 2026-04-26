<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RaceMarkColumn extends Model
{
    protected $fillable = [
        'race_id',
        'user_id',
        'column_type',
        'label',
        'display_order',
    ];

    /** @return BelongsTo<Race, $this> */
    public function race(): BelongsTo
    {
        return $this->belongsTo(Race::class);
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return HasMany<RaceMark, $this> */
    public function marks(): HasMany
    {
        return $this->hasMany(RaceMark::class);
    }
}
