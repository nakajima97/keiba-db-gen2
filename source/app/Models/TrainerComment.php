<?php

namespace App\Models;

use App\Enums\CommentOutcome;
use App\Enums\CommentSentiment;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * 出走馬に対する調教師のコメントとその当否（ユーザーごと）。
 *
 * @property CommentSentiment $sentiment
 * @property CommentOutcome|null $outcome
 */
class TrainerComment extends Model
{
    protected $fillable = [
        'user_id',
        'race_entry_id',
        'trainer_id',
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
     * 発言した調教師。
     *
     * @return BelongsTo<Trainer, $this>
     */
    public function speaker(): BelongsTo
    {
        return $this->belongsTo(Trainer::class, 'trainer_id');
    }
}
