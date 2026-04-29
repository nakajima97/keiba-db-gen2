<?php

namespace App\UseCases\HorseNote;

use App\Models\Horse;
use App\Models\HorseNote;
use App\Models\User;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Validation\ValidationException;

/**
 * 認証ユーザー所有の競走馬メモを 1 件作成する。
 * 同一ユーザー × 同一馬 × 同一 race_id（null 含む）の組み合わせで既にメモが存在する場合は ValidationException。
 */
class StoreAction
{
    /**
     * @return array{
     *     id: int,
     *     horse_id: int,
     *     race_id: int|null,
     *     race: array{uid: string, race_date: string, venue_name: string, race_number: int, race_name: string|null}|null,
     *     content: string,
     *     created_at: string,
     *     updated_at: string,
     * }
     *
     * @throws ValidationException
     */
    public function execute(Horse $horse, User $user, ?int $raceId, string $content): array
    {
        $duplicateExists = HorseNote::query()
            ->where('user_id', $user->id)
            ->where('horse_id', $horse->id)
            ->where(function ($query) use ($raceId) {
                if ($raceId === null) {
                    $query->whereNull('race_id');
                } else {
                    $query->where('race_id', $raceId);
                }
            })
            ->exists();

        if ($duplicateExists) {
            throw $this->duplicateException();
        }

        try {
            $note = HorseNote::create([
                'user_id' => $user->id,
                'horse_id' => $horse->id,
                'race_id' => $raceId,
                'content' => $content,
            ]);
        } catch (UniqueConstraintViolationException) {
            // exists() と create() の間に他リクエストが先に作成した場合のフォールバック
            throw $this->duplicateException();
        }

        $note->load(['race.venue']);

        return HorseNotePresenter::present($note);
    }

    private function duplicateException(): ValidationException
    {
        return ValidationException::withMessages([
            'content' => 'この競走馬・レースの組み合わせには既にメモが存在します。',
        ]);
    }
}
