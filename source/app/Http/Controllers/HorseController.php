<?php

namespace App\Http\Controllers;

use App\Models\Horse;
use App\UseCases\Horse\ShowAction;
use Inertia\Inertia;
use Inertia\Response;

class HorseController extends Controller
{
    public function show(Horse $horse, ShowAction $action): Response
    {
        return Inertia::render('horses/show', [
            'horse' => $action->execute($horse),
        ]);
    }
}
