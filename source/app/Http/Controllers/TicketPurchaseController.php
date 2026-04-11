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
                'ticket_purchases.id',
                'ticket_purchases.selections',
                'ticket_purchases.amount',
                'ticket_purchases.payout_amount',
                'races.uid as race_uid',
                'races.race_date',
                'venues.name as venue_name',
                'races.race_number',
                'ticket_types.label as ticket_type_label',
                'buy_types.name as buy_type_name',
            ])
            ->selectRaw('EXISTS(SELECT 1 FROM race_payouts WHERE race_payouts.race_id = races.id) as has_race_result')
            ->orderByDesc('race_date')
            ->orderByDesc('venue_name')
            ->orderByDesc('race_number')
            ->cursorPaginate(30);

        $purchases = $paginator->map(fn (TicketPurchase $purchase) => [
            'id' => $purchase->id,
            'race_uid' => $purchase->race_uid,
            'has_race_result' => (bool) $purchase->has_race_result,
            'race_date' => $purchase->race_date,
            'venue_name' => $purchase->venue_name,
            'race_number' => $purchase->race_number,
            'ticket_type_label' => $purchase->ticket_type_label,
            'buy_type_name' => $purchase->buy_type_name,
            'selections' => $purchase->selections,
            'amount' => $purchase->amount,
            'payout_amount' => $purchase->payout_amount !== null ? (int) $purchase->payout_amount : null,
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
