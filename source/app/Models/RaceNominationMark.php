<?php

namespace App\Models;

use Database\Factories\RaceNominationMarkFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * 登録馬に対するユーザーごとのチェック／消し。
 */
class RaceNominationMark extends Model
{
    /** @use HasFactory<RaceNominationMarkFactory> */
    use HasFactory;

    public const STATUS_CHECK = 'check';

    public const STATUS_EXCLUDED = 'excluded';

    /** @var list<string> */
    public const STATUSES = [self::STATUS_CHECK, self::STATUS_EXCLUDED];

    protected $fillable = [
        'race_nomination_id',
        'user_id',
        'status',
    ];

    /** @return BelongsTo<RaceNomination, $this> */
    public function nomination(): BelongsTo
    {
        return $this->belongsTo(RaceNomination::class, 'race_nomination_id');
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
