<?php

namespace App\Http\Controllers;

use App\Http\Requests\RaceMarkColumn\StoreRaceMarkColumnRequest;
use App\Http\Requests\RaceMarkColumn\UpdateRaceMarkColumnRequest;
use App\Models\Race;
use App\Models\RaceNominationMarkColumn;
use App\UseCases\RaceNominationMarkColumn\DestroyAction;
use App\UseCases\RaceNominationMarkColumn\IndexAction;
use App\UseCases\RaceNominationMarkColumn\StoreAction;
use App\UseCases\RaceNominationMarkColumn\UpdateAction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class RaceNominationMarkColumnController extends Controller
{
    public function index(Race $race, Request $request, IndexAction $action): JsonResponse
    {
        return response()->json([
            'data' => $action->execute($race, $request->user()),
        ]);
    }

    public function store(Race $race, StoreRaceMarkColumnRequest $request, StoreAction $action): JsonResponse
    {
        $data = $action->execute(
            $race,
            $request->user(),
            (string) ($request->validated('label') ?? ''),
        );

        return response()->json(['data' => $data], 201);
    }

    public function update(
        Race $race,
        int $id,
        UpdateRaceMarkColumnRequest $request,
        UpdateAction $action,
    ): JsonResponse {
        $column = RaceNominationMarkColumn::query()
            ->where('race_id', $race->id)
            ->findOrFail($id);

        $data = $action->execute(
            $column,
            $request->user(),
            (string) ($request->validated('label') ?? ''),
        );

        return response()->json(['data' => $data]);
    }

    public function destroy(Race $race, int $id, Request $request, DestroyAction $action): Response
    {
        $column = RaceNominationMarkColumn::query()
            ->where('race_id', $race->id)
            ->findOrFail($id);

        $action->execute($column, $request->user());

        return response()->noContent();
    }
}
