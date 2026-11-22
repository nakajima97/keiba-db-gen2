<?php

namespace App\Models;

use Database\Factories\TrainerNoteFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TrainerNote extends Model
{
    /** @use HasFactory<TrainerNoteFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'trainer_id',
        'content',
    ];

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return BelongsTo<Trainer, $this> */
    public function trainer(): BelongsTo
    {
        return $this->belongsTo(Trainer::class);
    }
}
