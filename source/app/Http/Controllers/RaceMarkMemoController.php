<?php

namespace App\Http\Controllers;

use App\Http\Requests\RaceMarkMemo\UpsertRaceMarkMemoRequest;
use App\Models\Race;
use App\Models\RaceEntry;
use App\Models\RaceMarkColumn;
use App\UseCases\RaceMarkMemo\DestroyAction;
use App\UseCases\RaceMarkMemo\UpsertAction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class RaceMarkMemoController extends Controller
{
    public function upsert(
        Race $race,
        int $columnId,
        int $raceEntryId,
        UpsertRaceMarkMemoRequest $request,
        UpsertAction $action,
    ): JsonResponse {
        $column = RaceMarkColumn::query()
            ->where('race_id', $race->id)
            ->findOrFail($columnId);
        RaceEntry::query()
            ->where('race_id', $race->id)
            ->findOrFail($raceEntryId);

        $result = $action->execute(
            $column,
            $raceEntryId,
            $request->user(),
            (string) $request->validated('content'),
        );

        return response()->json(
            ['data' => $result['memo']],
            $result['created'] ? 201 : 200,
        );
    }

    public function destroy(
        Race $race,
        int $columnId,
        int $raceEntryId,
        Request $request,
        DestroyAction $action,
    ): Response {
        $column = RaceMarkColumn::query()
            ->where('race_id', $race->id)
            ->findOrFail($columnId);

        $action->execute($column, $raceEntryId, $request->user());

        return response()->noContent();
    }
}
