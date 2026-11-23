<?php

namespace App\UseCases\Race;

use App\Enums\CommentSpeakerType;
use App\Models\Race;
use App\Models\RaceMarkColumn;
use App\Models\RaceMarkMemo;
use App\Models\User;
use App\UseCases\EntryComment\LoadCommentsByEntryId;
use App\UseCases\EntryComment\LoadStatsBySpeakerId;
use App\UseCases\HorseNote\LoadNotesByHorseId;
use App\UseCases\RaceMarkColumn\EnsureOwnColumnExistsAction;
use App\UseCases\RaceNomination\LoadNominationsAction;
use App\UseCases\TrainerNote\LoadNotesByTrainerId;
use Carbon\CarbonInterface;

/**
 * レースと出馬表（馬・騎手・調教師含む）に加え、認証ユーザーの印列・印データ・競走馬メモ・調教師メモ・調教師/騎手コメントと的中率、登録馬を取得し、
 * レース詳細画面の表示用データを返す。
 */
class ShowAction
{
    public function __construct(
        private LoadNotesByHorseId $loadNotesByHorseId,
        private EnsureOwnColumnExistsAction $ensureOwnColumnExistsAction,
        private LoadNotesByTrainerId $loadNotesByTrainerId,
        private LoadNominationsAction $loadNominationsAction,
        private LoadCommentsByEntryId $loadCommentsByEntryId,
        private LoadStatsBySpeakerId $loadStatsBySpeakerId,
    ) {}

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
     *         uid: string,
     *         frame_number: int,
     *         horse_number: int,
     *         horse_id: int,
     *         horse_uid: string,
     *         horse_name: string,
     *         jockey_id: int,
     *         jockey_name: string,
     *         trainer_id: int|null,
     *         trainer_name: string|null,
     *         trainer_affiliation: string|null,
     *         trainer_note: array{id: int, content: string}|null,
     *         weight: int|null,
     *         note: array{id: int, content: string, source: string}|null,
     *         comments: list<array<string, mixed>>,
     *         trainer_comment_stats: array<string, array{hit: int, judged: int}>|null,
     *         jockey_comment_stats: array<string, array{hit: int, judged: int}>,
     *     }>,
     *     mark_columns: array<int, array{id: int, type: string, label: string|null, display_order: int}>,
     *     marks: array<int, array{column_id: int, race_entry_id: int, mark_value: string}>,
     *     mark_memos: array<int, array{column_id: int, race_entry_id: int, content: string}>,
     *     nominations: list<array{
     *         id: int,
     *         horse_id: int,
     *         horse_uid: string,
     *         horse_name: string,
     *         status: string|null,
     *         note: array{id: int, content: string, source: string}|null,
     *     }>,
     *     has_result: bool
     * }
     */
    public function execute(Race $race, User $user): array
    {
        $race->load([
            'venue',
            'entries' => fn ($query) => $query->orderBy('horse_number'),
            'entries.horse',
            'entries.jockey',
            'entries.trainer',
        ]);

        $this->ensureOwnColumnExistsAction->execute($race, $user);

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

        $columnIds = $columns->pluck('id')->all();
        $markMemos = RaceMarkMemo::query()
            ->whereIn('race_mark_column_id', $columnIds)
            ->get(['race_mark_column_id', 'race_entry_id', 'content'])
            ->map(fn (RaceMarkMemo $memo): array => [
                'column_id' => (int) $memo->race_mark_column_id,
                'race_entry_id' => (int) $memo->race_entry_id,
                'content' => (string) $memo->content,
            ])
            ->all();

        $horseIds = $race->entries
            ->map(fn ($entry) => (int) $entry->horse->id)
            ->unique()
            ->values()
            ->all();

        $notesByHorseId = $this->loadNotesByHorseId->execute($user, $horseIds, (int) $race->id);

        $trainerIds = $race->entries
            ->pluck('trainer_id')
            ->filter()
            ->map(fn ($trainerId): int => (int) $trainerId)
            ->unique()
            ->values()
            ->all();

        $notesByTrainerId = $this->loadNotesByTrainerId->execute($user, $trainerIds);

        $commentsByEntryId = $this->loadCommentsByEntryId->execute(
            $user,
            $race->entries->map(fn ($entry): int => (int) $entry->id)->values()->all(),
        );
        $trainerStats = $this->loadStatsBySpeakerId->execute($user, CommentSpeakerType::Trainer, $trainerIds);
        $jockeyIds = $race->entries
            ->map(fn ($entry): int => (int) $entry->jockey_id)
            ->unique()
            ->values()
            ->all();
        $jockeyStats = $this->loadStatsBySpeakerId->execute($user, CommentSpeakerType::Jockey, $jockeyIds);

        return [
            'id' => (int) $race->id,
            'uid' => $race->uid,
            'race_date' => $race->race_date instanceof CarbonInterface
                ? $race->race_date->format('Y-m-d')
                : (string) $race->race_date,
            'venue_name' => $race->venue->name,
            'race_number' => (int) $race->race_number,
            'race_name' => $race->race_name,
            'entries' => $race->entries->map(fn ($entry) => [
                'id' => (int) $entry->id,
                'uid' => (string) $entry->uid,
                'frame_number' => (int) $entry->frame_number,
                'horse_number' => (int) $entry->horse_number,
                'horse_id' => (int) $entry->horse->id,
                'horse_uid' => (string) $entry->horse->uid,
                'horse_name' => $entry->horse->name,
                'jockey_id' => (int) $entry->jockey_id,
                'jockey_name' => $entry->jockey->name,
                'trainer_id' => $entry->trainer_id !== null ? (int) $entry->trainer_id : null,
                'trainer_name' => $entry->trainer?->name,
                'trainer_affiliation' => $entry->trainer?->affiliation,
                'trainer_note' => $entry->trainer_id !== null
                    ? ($notesByTrainerId[(int) $entry->trainer_id] ?? null)
                    : null,
                'weight' => $entry->horse_weight !== null ? (int) $entry->horse_weight : null,
                'note' => $notesByHorseId[(int) $entry->horse->id] ?? null,
                'comments' => $commentsByEntryId[(int) $entry->id] ?? [],
                'trainer_comment_stats' => $entry->trainer_id !== null
                    ? $trainerStats[(int) $entry->trainer_id]
                    : null,
                'jockey_comment_stats' => $jockeyStats[(int) $entry->jockey_id],
            ])->all(),
            'mark_columns' => $columns->map(fn (RaceMarkColumn $column): array => [
                'id' => (int) $column->id,
                'type' => $column->column_type,
                'label' => $column->column_type === 'own' ? null : ($column->label ?? ''),
                'display_order' => (int) $column->display_order,
            ])->all(),
            'marks' => $marks,
            'mark_memos' => $markMemos,
            'nominations' => $this->loadNominationsAction->execute($race, $user),
            'has_result' => $race->raceResultHorses()->exists() || $race->racePayouts()->exists(),
        ];
    }
}
