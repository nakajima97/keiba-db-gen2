<?php

namespace App\Models;

use Database\Factories\RaceNominationFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * 特別登録・出走登録など、出走確定前の登録馬。全ユーザー共通の事実データ。
 */
class RaceNomination extends Model
{
    /** @use HasFactory<RaceNominationFactory> */
    use HasFactory;

    protected $fillable = [
        'race_id',
        'horse_id',
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

    /** @return HasMany<RaceNominationMark, $this> */
    public function marks(): HasMany
    {
        return $this->hasMany(RaceNominationMark::class);
    }
}
