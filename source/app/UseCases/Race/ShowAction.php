<?php

namespace App\UseCases\Race;

use App\Models\HorseNote;
use App\Models\Race;
use App\Models\RaceMarkColumn;
use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Database\UniqueConstraintViolationException;

/**
 * レースと出馬表（馬・騎手含む）に加え、認証ユーザーの印列・印データ・競走馬メモを取得し、
 * レース詳細画面の表示用データを返す。
 */
class ShowAction
{
    /**
     * @return array{
     *     id: int,
     *     uid: string,
     *     race_date: string,
     *     venue_name: string,
     *     race_number: int,
     *     race_name: string|null,
     *     entries: array<int, array{
     *         id: int,
     *         frame_number: int,
     *         horse_number: int,
     *         horse_id: int,
     *         horse_name: string,
     *         jockey_name: string,
     *         weight: int|null,
     *         note: array{id: int, content: string, source: string}|null,
     *     }>,
     *     mark_columns: array<int, array{id: int, type: string, label: string|null, display_order: int}>,
     *     marks: array<int, array{column_id: int, race_entry_id: int, mark_value: string}>
     * }
     */
    public function execute(Race $race, User $user): array
    {
        $race->load([
            'venue',
            'raceEntries' => fn ($query) => $query->orderBy('horse_number'),
            'raceEntries.horse',
            'raceEntries.jockey',
        ]);

        $this->ensureOwnColumnExists($race, $user);

        $columns = RaceMarkColumn::query()
            ->where('race_id', $race->id)
            ->where('user_id', $user->id)
            ->orderBy('display_order')
            ->with('marks')
            ->get();

        $marks = [];
        foreach ($columns as $column) {
            foreach ($column->marks as $mark) {
                $marks[] = [
                    'column_id' => (int) $column->id,
                    'race_entry_id' => (int) $mark->race_entry_id,
                    'mark_value' => $mark->mark_value,
                ];
            }
        }

        $horseIds = $race->raceEntries
            ->map(fn ($entry) => (int) $entry->horse->id)
            ->unique()
            ->values()
            ->all();

        $notesByHorseId = $this->loadNotesByHorseId($user, $horseIds, $race->id);

        return [
            'id' => (int) $race->id,
            'uid' => $race->uid,
            'race_date' => $race->race_date instanceof CarbonInterface
                ? $race->race_date->format('Y-m-d')
                : (string) $race->race_date,
            'venue_name' => $race->venue->name,
            'race_number' => (int) $race->race_number,
            'race_name' => $race->race_name,
            'entries' => $race->raceEntries->map(function ($entry) use ($notesByHorseId) {
                $horseId = (int) $entry->horse->id;

                return [
                    'id' => (int) $entry->id,
                    'frame_number' => (int) $entry->frame_number,
                    'horse_number' => (int) $entry->horse_number,
                    'horse_id' => $horseId,
                    'horse_name' => $entry->horse->name,
                    'jockey_name' => $entry->jockey->name,
                    'weight' => $entry->horse_weight !== null ? (int) $entry->horse_weight : null,
                    'note' => $notesByHorseId[$horseId] ?? null,
                ];
            })->all(),
            'mark_columns' => $columns->map(fn (RaceMarkColumn $column): array => [
                'id' => (int) $column->id,
                'type' => $column->column_type,
                'label' => $column->column_type === 'own' ? null : ($column->label ?? ''),
                'display_order' => (int) $column->display_order,
            ])->all(),
            'marks' => $marks,
        ];
    }

    /**
     * @param  list<int>  $horseIds
     * @return array<int, array{id: int, content: string, source: string}>
     */
    private function loadNotesByHorseId(User $user, array $horseIds, int $raceId): array
    {
        if ($horseIds === []) {
            return [];
        }

        $notes = HorseNote::query()
            ->where('user_id', $user->id)
            ->whereIn('horse_id', $horseIds)
            ->where(function ($query) use ($raceId) {
                $query->where('race_id', $raceId)->orWhereNull('race_id');
            })
            ->get();

        $byHorseId = [];
        foreach ($notes as $note) {
            $horseId = (int) $note->horse_id;
            $isRaceLinked = $note->race_id !== null;
            $candidate = [
                'id' => (int) $note->id,
                'content' => $note->content,
                'source' => $isRaceLinked ? 'race' : 'horse',
            ];

            // race-linked メモを優先。既に race-linked が入っていれば上書きしない。
            if (isset($byHorseId[$horseId]) && $byHorseId[$horseId]['source'] === 'race') {
                continue;
            }

            $byHorseId[$horseId] = $candidate;
        }

        return $byHorseId;
    }

    private function ensureOwnColumnExists(Race $race, User $user): void
    {
        try {
            RaceMarkColumn::firstOrCreate(
                [
                    'race_id' => $race->id,
                    'user_id' => $user->id,
                    'column_type' => 'own',
                ],
                [
                    'label' => null,
                    'display_order' => 0,
                ],
            );
        } catch (UniqueConstraintViolationException) {
            // 別リクエストが先に作成済み。次のクエリで読めるので無視。
        }
    }
}
