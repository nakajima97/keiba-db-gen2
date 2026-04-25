<?php

namespace App\Http\Controllers;

use App\Exceptions\RaceResult\ParseException;
use App\Http\Requests\RaceResult\StoreRaceResultRequest;
use App\UseCases\RaceResult\ShowAction;
use App\UseCases\RaceResult\ShowResultAction;
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
        } catch (ParseException $e) {
            return redirect()->back()->withErrors([$e->field => $e->getMessage()])->withInput();
        }

        return redirect()->route('tickets.index');
    }

    public function edit(string $uid, ShowResultAction $action): Response
    {
        return Inertia::render('races/result/edit', [
            'race' => $action->execute($uid),
        ]);
    }
}
