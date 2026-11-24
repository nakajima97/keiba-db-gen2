<?php

namespace App\Models;

use Database\Factories\RaceNominationColumnMarkFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * 登録馬に対する他人の印。印列は出馬表と共通の race_mark_columns を使う。
 */
class RaceNominationColumnMark extends Model
{
    /** @use HasFactory<RaceNominationColumnMarkFactory> */
    use HasFactory;

    protected $fillable = [
        'race_mark_column_id',
        'race_nomination_id',
        'mark_value',
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
