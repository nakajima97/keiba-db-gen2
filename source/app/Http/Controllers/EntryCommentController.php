<?php

namespace App\Http\Controllers;

use App\Enums\CommentSpeakerType;
use App\Http\Requests\EntryComment\StoreEntryCommentRequest;
use App\Http\Requests\EntryComment\UpdateEntryCommentRequest;
use App\Models\Race;
use App\Models\RaceEntry;
use App\UseCases\EntryComment\DestroyAction;
use App\UseCases\EntryComment\LoadEntryStatsAction;
use App\UseCases\EntryComment\StoreAction;
use App\UseCases\EntryComment\UpdateAction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EntryCommentController extends Controller
{
    public function store(
        Race $race,
        int $raceEntryId,
        StoreEntryCommentRequest $request,
        StoreAction $action,
        LoadEntryStatsAction $loadEntryStats,
    ): JsonResponse {
        $entry = $this->findEntry($race, $raceEntryId);
        $comment = $action->execute($entry, $request->user(), $request->validated());

        return response()->json([
            'data' => $comment,
            'stats' => $loadEntryStats->execute($request->user(), $entry),
        ], 201);
    }

    public function update(
        Race $race,
        int $raceEntryId,
        CommentSpeakerType $speakerType,
        int $commentId,
        UpdateEntryCommentRequest $request,
        UpdateAction $action,
        LoadEntryStatsAction $loadEntryStats,
    ): JsonResponse {
        $entry = $this->findEntry($race, $raceEntryId);
        $comment = $action->execute($entry, $request->user(), $speakerType, $commentId, $request->validated());

        return response()->json([
            'data' => $comment,
            'stats' => $loadEntryStats->execute($request->user(), $entry),
        ]);
    }

    public function destroy(
        Race $race,
        int $raceEntryId,
        CommentSpeakerType $speakerType,
        int $commentId,
        Request $request,
        DestroyAction $action,
        LoadEntryStatsAction $loadEntryStats,
    ): JsonResponse {
        $entry = $this->findEntry($race, $raceEntryId);
        $action->execute($entry, $request->user(), $speakerType, $commentId);

        return response()->json([
            'stats' => $loadEntryStats->execute($request->user(), $entry),
        ]);
    }

    private function findEntry(Race $race, int $raceEntryId): RaceEntry
    {
        return RaceEntry::query()
            ->where('race_id', $race->id)
            ->findOrFail($raceEntryId);
    }
}
