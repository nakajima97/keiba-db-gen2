<?php

namespace App\UseCases\TrainerNote;

use App\Models\Trainer;
use App\Models\TrainerNote;
use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\DB;

/**
 * 認証ユーザーの調教師メモを upsert する。メモはユーザー × 調教師で 1 件。
 */
class UpsertAction
{
    /**
     * @return array{
     *     created: bool,
     *     note: array{
     *         id: int,
     *         trainer_id: int,
     *         content: string,
     *         created_at: string|null,
     *         updated_at: string|null,
     *     }
     * }
     */
    public function execute(Trainer $trainer, User $user, string $content): array
    {
        // 並行リクエストでのユニーク制約違反を避けるため、行ロックを取って upsert する。
        return DB::transaction(function () use ($trainer, $user, $content): array {
            $existing = TrainerNote::query()
                ->where('user_id', $user->id)
                ->where('trainer_id', $trainer->id)
                ->lockForUpdate()
                ->first();

            if ($existing !== null) {
                $existing->update(['content' => $content]);

                return [
                    'created' => false,
                    'note' => $this->present($existing),
                ];
            }

            $note = TrainerNote::create([
                'user_id' => $user->id,
                'trainer_id' => $trainer->id,
                'content' => $content,
            ]);

            return [
                'created' => true,
                'note' => $this->present($note),
            ];
        });
    }

    /**
     * @return array{
     *     id: int,
     *     trainer_id: int,
     *     content: string,
     *     created_at: string|null,
     *     updated_at: string|null,
     * }
     */
    private function present(TrainerNote $note): array
    {
        return [
            'id' => (int) $note->id,
            'trainer_id' => (int) $note->trainer_id,
            'content' => (string) $note->content,
            'created_at' => $note->created_at instanceof CarbonInterface
                ? $note->created_at->toIso8601String()
                : null,
            'updated_at' => $note->updated_at instanceof CarbonInterface
                ? $note->updated_at->toIso8601String()
                : null,
        ];
    }
}
