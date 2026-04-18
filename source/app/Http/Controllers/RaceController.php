<?php

namespace App\Http\Controllers;

use App\Http\Requests\Race\StoreRaceRequest;
use App\Models\Venue;
use App\UseCases\Race\StoreAction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class RaceController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('races/index');
    }

    public function create(Request $request): Response
    {
        return Inertia::render('races/new', [
            'venues' => Venue::query()->orderBy('id')->get(['id', 'name']),
            'last_venue_id' => $request->session()->get('last_venue_id'),
            'last_race_date' => $request->session()->get('last_race_date'),
            'last_race_number' => $request->session()->get('last_race_number'),
        ]);
    }

    public function store(StoreRaceRequest $request, StoreAction $action): RedirectResponse
    {
        $action->execute($request->validated());

        return redirect()
            ->route('races.create')
            ->with([
                'last_venue_id' => (int) $request->validated('venue_id'),
                'last_race_date' => (string) $request->validated('race_date'),
                'last_race_number' => (int) $request->validated('race_number'),
            ]);
    }
}
