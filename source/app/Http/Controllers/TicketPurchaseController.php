<?php

namespace App\Http\Controllers;

use App\Http\Requests\TicketPurchase\StoreTicketPurchaseRequest;
use App\UseCases\TicketPurchase\StoreAction;
use Illuminate\Http\JsonResponse;

class TicketPurchaseController extends Controller
{
    public function store(StoreTicketPurchaseRequest $request, StoreAction $action): JsonResponse
    {
        $action->execute($request->validated(), $request->user()->id);

        return response()->json(null, 201);
    }
}
