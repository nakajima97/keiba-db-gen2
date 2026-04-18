<?php

namespace App\Http\Controllers;

use App\Http\Requests\Race\StoreRaceRequest;
use App\Models\Race;
use App\Models\Venue;
use App\UseCases\Race\StoreAction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class RaceController extends Controller
{
    public function index(Request $request): Response
    {
        $venueId = $request->query('venue_id');
        $raceDate = $request->query('race_date');

        return Inertia::render('races/index', [
            'races' => $raceDate ? Race::query()
                ->with('venue')
                ->where('race_date', $raceDate)
                ->when($venueId, fn ($q, $id) => $q->where('venue_id', $id))
                ->orderBy('venue_id')
                ->orderBy('race_number')
                ->get()
                ->map(fn (Race $race) => [
                    'uid' => $race->uid,
                    'race_date' => $race->race_date instanceof \Carbon\CarbonInterface
                        ? $race->race_date->format('Y-m-d')
                        : (string) $race->race_date,
                    'venue_name' => $race->venue->name,
                    'race_number' => $race->race_number,
                ]) : [],
            'venues' => Venue::query()->orderBy('id')->get(['id', 'name']),
            'filters' => [
                'race_date' => $raceDate,
                'venue_id' => $venueId !== null ? (int) $venueId : null,
            ],
        ]);
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
