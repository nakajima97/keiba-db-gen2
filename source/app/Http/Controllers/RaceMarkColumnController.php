<?php

namespace App\Http\Controllers;

use App\Http\Requests\RaceMarkColumn\StoreRaceMarkColumnRequest;
use App\Http\Requests\RaceMarkColumn\UpdateRaceMarkColumnRequest;
use App\Models\Race;
use App\Models\RaceMarkColumn;
use App\UseCases\RaceMarkColumn\DestroyAction;
use App\UseCases\RaceMarkColumn\IndexAction;
use App\UseCases\RaceMarkColumn\StoreAction;
use App\UseCases\RaceMarkColumn\UpdateAction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class RaceMarkColumnController extends Controller
{
    public function index(string $uid, Request $request, IndexAction $action): JsonResponse
    {
        $race = Race::query()->where('uid', $uid)->firstOrFail();

        return response()->json([
            'data' => $action->execute($race, $request->user()),
        ]);
    }

    public function store(string $uid, StoreRaceMarkColumnRequest $request, StoreAction $action): JsonResponse
    {
        $race = Race::query()->where('uid', $uid)->firstOrFail();

        $data = $action->execute(
            $race,
            $request->user(),
            (string) ($request->validated('label') ?? ''),
        );

        return response()->json(['data' => $data], 201);
    }

    public function update(
        string $uid,
        int $id,
        UpdateRaceMarkColumnRequest $request,
        UpdateAction $action,
    ): JsonResponse {
        $race = Race::query()->where('uid', $uid)->firstOrFail();
        $column = RaceMarkColumn::query()
            ->where('race_id', $race->id)
            ->findOrFail($id);

        $data = $action->execute(
            $column,
            $request->user(),
            (string) ($request->validated('label') ?? ''),
        );

        return response()->json(['data' => $data]);
    }

    public function destroy(string $uid, int $id, Request $request, DestroyAction $action): Response
    {
        $race = Race::query()->where('uid', $uid)->firstOrFail();
        $column = RaceMarkColumn::query()
            ->where('race_id', $race->id)
            ->findOrFail($id);

        $action->execute($column, $request->user());

        return response()->noContent();
    }
}
