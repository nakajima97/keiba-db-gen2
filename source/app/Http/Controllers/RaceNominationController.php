<?php

namespace App\Http\Controllers;

use App\Http\Requests\RaceNomination\StoreRaceNominationRequest;
use App\Http\Requests\RaceNomination\UpsertRaceNominationMarkRequest;
use App\Models\Race;
use App\Models\RaceNomination;
use App\UseCases\RaceNomination\DestroyAction;
use App\UseCases\RaceNomination\StoreAction;
use App\UseCases\RaceNomination\UpsertMarkAction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class RaceNominationController extends Controller
{
    public function store(Race $race, StoreRaceNominationRequest $request, StoreAction $action): JsonResponse
    {
        $horseId = $request->validated('horse_id');
        $horseName = $request->validated('horse_name');

        $data = $action->execute(
            $race,
            $request->user(),
            $horseId !== null ? (int) $horseId : null,
            $horseName !== null ? (string) $horseName : null,
        );

        return response()->json(['data' => $data], 201);
    }

    public function destroy(Race $race, int $nominationId, DestroyAction $action): Response
    {
        $nomination = RaceNomination::query()
            ->where('race_id', $race->id)
            ->findOrFail($nominationId);

        $action->execute($nomination);

        return response()->noContent();
    }

    public function upsertMark(
        Race $race,
        int $nominationId,
        UpsertRaceNominationMarkRequest $request,
        UpsertMarkAction $action,
    ): JsonResponse|Response {
        $nomination = RaceNomination::query()
            ->where('race_id', $race->id)
            ->findOrFail($nominationId);

        $result = $action->execute(
            $nomination,
            $request->user(),
            (string) $request->validated('status'),
        );

        if ($result === null) {
            return response()->noContent();
        }

        return response()->json(['data' => $result]);
    }
}
