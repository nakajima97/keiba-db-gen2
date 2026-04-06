<?php

namespace App\Http\Controllers;

use App\Http\Requests\TicketPurchase\StoreTicketPurchaseRequest;
use App\UseCases\TicketPurchase\StoreAction;
use Illuminate\Http\RedirectResponse;

class TicketPurchaseController extends Controller
{
    public function store(StoreTicketPurchaseRequest $request, StoreAction $action): RedirectResponse
    {
        $action->execute($request->validated(), $request->user()->id);

        return redirect()->route('tickets.new');
    }
}
