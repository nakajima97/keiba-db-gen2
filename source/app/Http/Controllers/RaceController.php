<?php

namespace App\Http\Controllers;

use App\Http\Requests\Race\StoreRaceRequest;
use App\Models\Race;
use App\UseCases\Race\CreateAction;
use App\UseCases\Race\IndexAction;
use App\UseCases\Race\ShowAction;
use App\UseCases\Race\StoreAction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class RaceController extends Controller
{
    public function index(Request $request, IndexAction $action): Response
    {
        $venueId = $request->query('venue_id');
        $raceDate = $request->query('race_date');

        return Inertia::render('races/index', $action->execute(
            $raceDate !== null ? (string) $raceDate : null,
            $venueId !== null ? (int) $venueId : null,
        ));
    }

    public function create(Request $request, CreateAction $action): Response
    {
        return Inertia::render('races/new', $action->execute(
            $request->session()->get('last_venue_id'),
            $request->session()->get('last_race_date'),
            $request->session()->get('last_race_number'),
        ));
    }

    public function store(StoreRaceRequest $request, StoreAction $action): RedirectResponse
    {
        $action->execute($request->validated());

        $raceNumber = (int) $request->validated('race_number');

        return redirect()
            ->route('races.create')
            ->with([
                'last_venue_id' => (int) $request->validated('venue_id'),
                'last_race_date' => (string) $request->validated('race_date'),
                'last_race_number' => min($raceNumber + 1, 12),
            ]);
    }

    public function show(Race $race, Request $request, ShowAction $action): Response
    {
        return Inertia::render('races/show', [
            'race' => $action->execute($race, $request->user()),
        ]);
    }
}
