<?php

namespace App\Models;

use Database\Factories\HorseNoteFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HorseNote extends Model
{
    /** @use HasFactory<HorseNoteFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'horse_id',
        'race_id',
        'content',
    ];

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return BelongsTo<Horse, $this> */
    public function horse(): BelongsTo
    {
        return $this->belongsTo(Horse::class);
    }

    /** @return BelongsTo<Race, $this> */
    public function race(): BelongsTo
    {
        return $this->belongsTo(Race::class);
    }
}
