<?php

namespace App\Http\Controllers;

use App\Http\Requests\RaceMark\UpsertRaceMarkRequest;
use App\Models\Race;
use App\Models\RaceNomination;
use App\Models\RaceNominationMarkColumn;
use App\UseCases\RaceNominationColumnMark\UpsertAction;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class RaceNominationColumnMarkController extends Controller
{
    public function upsert(
        Race $race,
        int $columnId,
        int $nominationId,
        UpsertRaceMarkRequest $request,
        UpsertAction $action,
    ): JsonResponse|Response {
        $column = RaceNominationMarkColumn::query()
            ->where('race_id', $race->id)
            ->findOrFail($columnId);
        RaceNomination::query()
            ->where('race_id', $race->id)
            ->findOrFail($nominationId);

        $result = $action->execute(
            $column,
            $nominationId,
            $request->user(),
            (string) $request->validated('mark_value'),
        );

        if ($result === null) {
            return response()->noContent();
        }

        return response()->json(['data' => $result]);
    }
}
