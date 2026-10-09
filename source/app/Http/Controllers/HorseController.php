<?php

namespace App\Http\Controllers;

use App\Models\Horse;
use App\UseCases\Horse\IndexAction;
use App\UseCases\Horse\ShowAction;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class HorseController extends Controller
{
    public function index(Request $request, IndexAction $action): Response
    {
        $keyword = $request->query('keyword');

        return Inertia::render('horses/index', $action->execute(
            is_string($keyword) ? $keyword : null,
        ));
    }

    public function show(Horse $horse, Request $request, ShowAction $action): Response
    {
        return Inertia::render('horses/show', [
            'horse' => $action->execute($horse, $request->user()),
        ]);
    }
}
