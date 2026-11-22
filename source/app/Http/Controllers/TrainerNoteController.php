<?php

namespace App\Http\Controllers;

use App\Http\Requests\TrainerNote\UpsertTrainerNoteRequest;
use App\Models\Trainer;
use App\UseCases\TrainerNote\DestroyAction;
use App\UseCases\TrainerNote\UpsertAction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class TrainerNoteController extends Controller
{
    public function upsert(Trainer $trainer, UpsertTrainerNoteRequest $request, UpsertAction $action): JsonResponse
    {
        $result = $action->execute(
            $trainer,
            $request->user(),
            (string) $request->validated('content'),
        );

        return response()->json(
            ['data' => $result['note']],
            $result['created'] ? 201 : 200,
        );
    }

    public function destroy(Trainer $trainer, Request $request, DestroyAction $action): Response
    {
        $action->execute($trainer, $request->user());

        return response()->noContent();
    }
}
