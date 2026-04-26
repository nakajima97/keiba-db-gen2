<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RaceMark extends Model
{
    protected $fillable = [
        'race_mark_column_id',
        'race_entry_id',
        'mark_value',
    ];

    /** @return BelongsTo<RaceMarkColumn, $this> */
    public function column(): BelongsTo
    {
        return $this->belongsTo(RaceMarkColumn::class, 'race_mark_column_id');
    }

    /** @return BelongsTo<RaceEntry, $this> */
    public function raceEntry(): BelongsTo
    {
        return $this->belongsTo(RaceEntry::class);
    }
}
