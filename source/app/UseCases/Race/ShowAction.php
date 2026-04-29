<?php

namespace App\UseCases\Race;

use App\Models\Race;
use App\Models\RaceMarkColumn;
use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Database\UniqueConstraintViolationException;

/**
 * レースと出馬表（馬・騎手含む）に加え、認証ユーザーの印列・印データを取得し、
 * レース詳細画面の表示用データを返す。
 */
class ShowAction
{
    /**
     * @return array{
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
     *         weight: int|null
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

        return [
            'uid' => $race->uid,
            'race_date' => $race->race_date instanceof CarbonInterface
                ? $race->race_date->format('Y-m-d')
                : (string) $race->race_date,
            'venue_name' => $race->venue->name,
            'race_number' => (int) $race->race_number,
            'race_name' => $race->race_name,
            'entries' => $race->raceEntries->map(fn ($entry) => [
                'id' => (int) $entry->id,
                'frame_number' => (int) $entry->frame_number,
                'horse_number' => (int) $entry->horse_number,
                'horse_id' => (int) $entry->horse->id,
                'horse_name' => $entry->horse->name,
                'jockey_name' => $entry->jockey->name,
                'weight' => $entry->horse_weight !== null ? (int) $entry->horse_weight : null,
            ])->all(),
            'mark_columns' => $columns->map(fn (RaceMarkColumn $column): array => [
                'id' => (int) $column->id,
                'type' => $column->column_type,
                'label' => $column->column_type === 'own' ? null : ($column->label ?? ''),
                'display_order' => (int) $column->display_order,
            ])->all(),
            'marks' => $marks,
        ];
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
