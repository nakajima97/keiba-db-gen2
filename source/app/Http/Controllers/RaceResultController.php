<?php

namespace App\Http\Controllers;

use App\Http\Requests\RaceResult\StoreRaceResultRequest;
use App\UseCases\RaceResult\ShowAction;
use App\UseCases\RaceResult\StoreAction;
use Illuminate\Http\RedirectResponse;
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
        } catch (\InvalidArgumentException $e) {
            return redirect()->back()->withErrors(['text' => $e->getMessage()])->withInput();
        }

        return redirect()->route('tickets.index');
    }

    public function edit(string $uid, ShowAction $action): Response
    {
        return Inertia::render('races/result/edit', [
            'race' => $action->execute($uid),
        ]);
    }
}
