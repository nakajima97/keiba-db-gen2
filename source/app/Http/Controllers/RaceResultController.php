<?php

namespace App\Http\Controllers;

use App\Http\Requests\RaceResult\StoreRaceResultRequest;
use App\Models\Race;
use App\UseCases\RaceResult\StoreAction;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class RaceResultController extends Controller
{
    public function create(string $uid): Response
    {
        $race = Race::where('uid', $uid)->with('venue')->firstOrFail();

        return Inertia::render('races/result/create', [
            'race' => [
                'uid' => $race->uid,
                'venue_name' => $race->venue->name,
                'race_date' => $race->race_date,
                'race_number' => $race->race_number,
            ],
        ]);
    }

    public function store(string $uid, StoreRaceResultRequest $request, StoreAction $action): RedirectResponse
    {
        $race = Race::where('uid', $uid)->firstOrFail();

        try {
            $action->execute($request->validated(), $race->id);
        } catch (\InvalidArgumentException $e) {
            return redirect()->back()->withErrors(['text' => $e->getMessage()])->withInput();
        }

        return redirect()->route('tickets.index');
    }

    public function edit(string $uid): Response
    {
        $race = Race::where('uid', $uid)->with('venue')->firstOrFail();

        return Inertia::render('races/result/edit', [
            'race' => [
                'uid' => $race->uid,
                'venue_name' => $race->venue->name,
                'race_date' => $race->race_date,
                'race_number' => $race->race_number,
            ],
        ]);
    }
}
