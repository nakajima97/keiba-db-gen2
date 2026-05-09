<?php

namespace App\Http\Controllers;

use App\Http\Requests\RaceEntry\AddSingleRaceEntryRequest;
use App\Http\Requests\RaceEntry\StoreRaceEntryRequest;
use App\Http\Requests\RaceEntry\UpdateRaceEntryRequest;
use App\Models\Race;
use App\Models\RaceEntry;
use App\UseCases\RaceEntry\AddSingleAction;
use App\UseCases\RaceEntry\StoreAction;
use App\UseCases\RaceEntry\UpdateAction;
use Carbon\CarbonInterface;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

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

    public function addCreate(Race $race): Response
    {
        $race->load('venue');

        return Inertia::render('races/entries/add', [
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

    public function addStore(Race $race, AddSingleRaceEntryRequest $request, AddSingleAction $action): RedirectResponse
    {
        /** @var array{
         *     horse_name: string,
         *     jockey_name: string,
         *     frame_number: int,
         *     horse_number: int,
         *     weight: float|string,
         *     horse_weight: int|string|null,
         * } $data
         */
        $data = $request->validated();

        $action->execute($race, $data);

        return redirect()->route('races.show', ['race' => $race->uid]);
    }

    public function edit(Race $race, RaceEntry $entry): Response
    {
        $this->ensureEntryBelongsToRace($entry, $race);

        $race->load('venue');
        $entry->load(['horse', 'jockey']);

        return Inertia::render('races/entries/edit', [
            'race_uid' => $race->uid,
            'entry_uid' => $entry->uid,
            'race_info' => [
                'race_date' => $race->race_date instanceof CarbonInterface
                    ? $race->race_date->format('Y-m-d')
                    : (string) $race->race_date,
                'venue_name' => $race->venue->name,
                'race_number' => (int) $race->race_number,
            ],
            'initial_values' => [
                'horse_name' => $entry->horse->name,
                'jockey_name' => $entry->jockey->name,
                'frame_number' => (int) $entry->frame_number,
                'horse_number' => (int) $entry->horse_number,
                'weight' => number_format((float) $entry->weight, 1, '.', ''),
                'horse_weight' => $entry->horse_weight !== null ? (string) $entry->horse_weight : '',
            ],
        ]);
    }

    public function update(
        Race $race,
        RaceEntry $entry,
        UpdateRaceEntryRequest $request,
        UpdateAction $action,
    ): RedirectResponse {
        $this->ensureEntryBelongsToRace($entry, $race);

        /** @var array{
         *     horse_name: string,
         *     jockey_name: string,
         *     frame_number: int,
         *     horse_number: int,
         *     weight: float|string,
         *     horse_weight: int|string|null,
         * } $data
         */
        $data = $request->validated();

        $action->execute($entry, $data);

        return redirect()->route('races.show', ['race' => $race->uid]);
    }

    private function ensureEntryBelongsToRace(RaceEntry $entry, Race $race): void
    {
        if ((int) $entry->race_id !== (int) $race->id) {
            throw new NotFoundHttpException;
        }
    }
}
