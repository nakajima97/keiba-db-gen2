<?php

namespace App\Models;

use App\Enums\CommentOutcome;
use App\Enums\CommentSentiment;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * 出走馬に対する騎手のコメントとその当否（ユーザーごと）。
 *
 * @property CommentSentiment $sentiment
 * @property CommentOutcome|null $outcome
 */
class JockeyComment extends Model
{
    protected $fillable = [
        'user_id',
        'race_entry_id',
        'jockey_id',
        'sentiment',
        'content',
        'outcome',
        'created_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'sentiment' => CommentSentiment::class,
            'outcome' => CommentOutcome::class,
        ];
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return BelongsTo<RaceEntry, $this> */
    public function raceEntry(): BelongsTo
    {
        return $this->belongsTo(RaceEntry::class);
    }

    /**
     * 発言した騎手。
     *
     * @return BelongsTo<Jockey, $this>
     */
    public function speaker(): BelongsTo
    {
        return $this->belongsTo(Jockey::class, 'jockey_id');
    }
}
