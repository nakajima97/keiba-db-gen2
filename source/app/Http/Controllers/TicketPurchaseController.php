<?php

namespace App\Http\Controllers;

use App\Http\Requests\TicketPurchase\StoreTicketPurchaseRequest;
use App\Models\TicketPurchase;
use App\UseCases\TicketPurchase\StoreAction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TicketPurchaseController extends Controller
{
    public function index(Request $request): Response
    {
        $paginator = TicketPurchase::query()
            ->where('ticket_purchases.user_id', $request->user()->id)
            ->leftJoin('races', 'ticket_purchases.race_id', '=', 'races.id')
            ->leftJoin('venues', 'races.venue_id', '=', 'venues.id')
            ->join('ticket_types', 'ticket_purchases.ticket_type_id', '=', 'ticket_types.id')
            ->join('buy_types', 'ticket_purchases.buy_type_id', '=', 'buy_types.id')
            ->select([
                'ticket_purchases.*',
                'races.race_date as race_date_sort',
                'venues.name as venue_name_sort',
                'races.race_number as race_number_sort',
            ])
            ->orderByDesc('race_date_sort')
            ->orderByDesc('venue_name_sort')
            ->orderByDesc('race_number_sort')
            ->with(['race.venue', 'ticketType', 'buyType'])
            ->cursorPaginate(30);

        $purchases = $paginator->map(fn (TicketPurchase $purchase) => [
            'id' => $purchase->id,
            'race_date' => $purchase->race?->race_date,
            'venue_name' => $purchase->race?->venue?->name,
            'race_number' => $purchase->race?->race_number,
            'ticket_type_label' => $purchase->ticketType->label,
            'buy_type_name' => $purchase->buyType->name,
            'selections' => $purchase->selections,
            'amount' => $purchase->amount,
        ]);

        return Inertia::render('tickets/index', [
            'purchases' => Inertia::merge(fn () => $purchases),
            'nextCursor' => $paginator->nextCursor()?->encode(),
        ]);
    }

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
