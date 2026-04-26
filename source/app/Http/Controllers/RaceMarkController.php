<?php

namespace App\Http\Controllers;

use App\Http\Requests\RaceMark\UpsertRaceMarkRequest;
use App\Models\Race;
use App\Models\RaceEntry;
use App\Models\RaceMarkColumn;
use App\UseCases\RaceMark\UpsertAction;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class RaceMarkController extends Controller
{
    public function upsert(
        string $uid,
        int $columnId,
        int $raceEntryId,
        UpsertRaceMarkRequest $request,
        UpsertAction $action,
    ): JsonResponse|Response {
        $race = Race::query()->where('uid', $uid)->firstOrFail();
        $column = RaceMarkColumn::query()
            ->where('race_id', $race->id)
            ->findOrFail($columnId);
        // race_entry の存在確認（404 を返す）
        RaceEntry::query()
            ->where('race_id', $race->id)
            ->findOrFail($raceEntryId);

        $result = $action->execute(
            $column,
            $raceEntryId,
            $request->user(),
            (string) ($request->validated('mark_value') ?? ''),
        );

        if ($result === null) {
            return response()->noContent();
        }

        return response()->json(['data' => $result]);
    }
}
