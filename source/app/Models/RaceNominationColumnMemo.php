<?php

namespace App\Models;

use Database\Factories\RaceNominationColumnMemoFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * 登録馬に対する他人の印の印メモ。印と独立したライフサイクルを持つ。
 */
class RaceNominationColumnMemo extends Model
{
    /** @use HasFactory<RaceNominationColumnMemoFactory> */
    use HasFactory;

    protected $fillable = [
        'race_mark_column_id',
        'race_nomination_id',
        'content',
    ];

    /** @return BelongsTo<RaceMarkColumn, $this> */
    public function column(): BelongsTo
    {
        return $this->belongsTo(RaceMarkColumn::class, 'race_mark_column_id');
    }

    /** @return BelongsTo<RaceNomination, $this> */
    public function nomination(): BelongsTo
    {
        return $this->belongsTo(RaceNomination::class, 'race_nomination_id');
    }
}
