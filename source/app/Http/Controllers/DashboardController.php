<?php

namespace App\Http\Controllers;

use App\UseCases\Balance\ShowDashboardAction;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function show(Request $request, ShowDashboardAction $action): Response
    {
        $year = $request->query('year');
        $year = $year !== null ? (int) $year : null;

        $data = $action->execute($request->user()->id, $year);

        return Inertia::render('dashboard', [
            'selected_year' => $data['selected_year'],
            'available_years' => $data['available_years'],
            'summary' => $data['summary'],
            'daily_balances' => $data['daily_balances'],
        ]);
    }
}
