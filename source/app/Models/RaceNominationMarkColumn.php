<?php

namespace App\Models;

use Database\Factories\RaceNominationMarkColumnFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * 登録馬用の他人の印列。出馬表の印列（race_mark_columns）とは独立して管理する。
 */
class RaceNominationMarkColumn extends Model
{
    /** @use HasFactory<RaceNominationMarkColumnFactory> */
    use HasFactory;

    protected $fillable = [
        'race_id',
        'user_id',
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

    /** @return HasMany<RaceNominationColumnMark, $this> */
    public function marks(): HasMany
    {
        return $this->hasMany(RaceNominationColumnMark::class);
    }

    /** @return HasMany<RaceNominationColumnMemo, $this> */
    public function memos(): HasMany
    {
        return $this->hasMany(RaceNominationColumnMemo::class);
    }
}
