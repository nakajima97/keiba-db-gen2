<?php

namespace App\Http\Controllers;

use App\Http\Requests\TicketPurchase\StoreTicketPurchaseRequest;
use App\UseCases\TicketPurchase\StoreAction;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class TicketPurchaseController extends Controller
{
    public function create(): Response
    {
        return Inertia::render('tickets/new', [
            'lastVenue' => session('last_venue') ?? '東京',
            'lastRaceDate' => session('last_race_date') ?? now()->toDateString(),
            'lastRaceNumber' => session('last_race_number') ?? 1,
        ]);
    }

    public function store(StoreTicketPurchaseRequest $request, StoreAction $action): RedirectResponse
    {
        $validated = $request->validated();

        $action->execute($validated, $request->user()->id);

        session()->flash('last_venue', $validated['venue']);
        session()->flash('last_race_date', $validated['race_date']);
        session()->flash('last_race_number', $validated['race_number']);

        return redirect()->route('tickets.new');
    }
}
