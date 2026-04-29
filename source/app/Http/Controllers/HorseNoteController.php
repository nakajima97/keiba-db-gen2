<?php

namespace App\Http\Controllers;

use App\Http\Requests\HorseNote\StoreHorseNoteRequest;
use App\Http\Requests\HorseNote\UpdateHorseNoteRequest;
use App\Models\Horse;
use App\Models\HorseNote;
use App\UseCases\HorseNote\DeleteAction;
use App\UseCases\HorseNote\IndexAction;
use App\UseCases\HorseNote\StoreAction;
use App\UseCases\HorseNote\UpdateAction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class HorseNoteController extends Controller
{
    public function index(Horse $horse, Request $request, IndexAction $action): JsonResponse
    {
        return response()->json([
            'data' => $action->execute($horse, $request->user()),
        ]);
    }

    public function store(Horse $horse, StoreHorseNoteRequest $request, StoreAction $action): JsonResponse
    {
        $data = $action->execute(
            $horse,
            $request->user(),
            $request->validated('race_id'),
            $request->validated('content'),
        );

        return response()->json(['data' => $data], 201);
    }

    public function update(HorseNote $note, UpdateHorseNoteRequest $request, UpdateAction $action): JsonResponse
    {
        $data = $action->execute(
            $note,
            $request->user(),
            $request->validated('content'),
        );

        return response()->json(['data' => $data]);
    }

    public function destroy(HorseNote $note, Request $request, DeleteAction $action): Response
    {
        $action->execute($note, $request->user());

        return response()->noContent();
    }
}
