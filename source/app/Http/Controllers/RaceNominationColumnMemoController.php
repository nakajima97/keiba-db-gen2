<?php

namespace App\Http\Controllers;

use App\Http\Requests\RaceMarkMemo\UpsertRaceMarkMemoRequest;
use App\Models\Race;
use App\Models\RaceNomination;
use App\Models\RaceNominationMarkColumn;
use App\UseCases\RaceNominationColumnMemo\DestroyAction;
use App\UseCases\RaceNominationColumnMemo\UpsertAction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class RaceNominationColumnMemoController extends Controller
{
    public function upsert(
        Race $race,
        int $columnId,
        int $nominationId,
        UpsertRaceMarkMemoRequest $request,
        UpsertAction $action,
    ): JsonResponse {
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
        int $nominationId,
        Request $request,
        DestroyAction $action,
    ): Response {
        $column = RaceNominationMarkColumn::query()
            ->where('race_id', $race->id)
            ->findOrFail($columnId);
        RaceNomination::query()
            ->where('race_id', $race->id)
            ->findOrFail($nominationId);

        $action->execute($column, $nominationId, $request->user());

        return response()->noContent();
    }
}
