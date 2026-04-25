<?php

namespace App\Http\Controllers;

use App\Http\Requests\RaceEntry\StoreRaceEntryRequest;
use App\Models\Race;
use App\UseCases\RaceEntry\StoreAction;
use Carbon\CarbonInterface;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class RaceEntryController extends Controller
{
    public function create(Race $race): Response
    {
        $race->load('venue');

        return Inertia::render('races/entries/new', [
            'race_uid' => $race->uid,
            'race_info' => [
                'race_date' => $race->race_date instanceof CarbonInterface
                    ? $race->race_date->format('Y-m-d')
                    : (string) $race->race_date,
                'venue_name' => $race->venue->name,
                'race_number' => (int) $race->race_number,
            ],
        ]);
    }

    public function store(Race $race, StoreRaceEntryRequest $request, StoreAction $action): RedirectResponse
    {
        $action->execute($race, (string) $request->validated('paste_text'));

        return redirect()->route('races.show', ['race' => $race->uid]);
    }
}
