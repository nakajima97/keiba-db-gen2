<?php

namespace App\Http\Controllers;

use App\Exceptions\RaceResult\NoResultToDestroyException;
use App\Exceptions\RaceResult\ParseException;
use App\Http\Requests\RaceResult\StoreRaceResultRequest;
use App\UseCases\RaceResult\DestroyAction;
use App\UseCases\RaceResult\ShowAction;
use App\UseCases\RaceResult\ShowResultAction;
use App\UseCases\RaceResult\StoreAction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class RaceResultController extends Controller
{
    public function create(string $uid, ShowAction $action): Response
    {
        return Inertia::render('races/result/create', [
            'race' => $action->execute($uid),
        ]);
    }

    public function store(string $uid, StoreRaceResultRequest $request, StoreAction $action): RedirectResponse
    {
        try {
            $action->execute($request->validated(), $uid, $request->user()->id);
        } catch (ParseException $e) {
            return redirect()->back()->withErrors([$e->field => $e->getMessage()])->withInput();
        }

        return redirect()->route('tickets.index');
    }

    public function edit(string $uid, Request $request, ShowResultAction $action): Response
    {
        return Inertia::render('races/result/edit', [
            'race' => $action->execute($uid, $request->user()),
        ]);
    }

    public function destroy(string $uid, DestroyAction $action): JsonResponse
    {
        try {
            $action->execute($uid);
        } catch (NoResultToDestroyException $e) {
            return response()->json(['message' => $e->getMessage()], 409);
        }

        return response()->json(['message' => 'レース結果を削除しました']);
    }
}
