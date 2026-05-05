<?php

namespace App\Http\Controllers;

use App\UseCases\Insights\ShowInsightsAction;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class InsightsController extends Controller
{
    public function index(Request $request, ShowInsightsAction $action): Response
    {
        $period = $request->query('period');
        $allowed = ['1m', '3m', '6m', '1y', 'all'];
        if (! is_string($period) || ! in_array($period, $allowed, true)) {
            $period = '1m';
        }

        $data = $action->execute($request->user()->id, $period);

        return Inertia::render('insights', [
            'period' => $period,
            'summary' => $data['summary'],
            'patternBreakdown' => $data['patternBreakdown'],
            'popularityPatternMatrix' => $data['popularityPatternMatrix'],
            'popularityReturns' => $data['popularityReturns'],
            'monthlyTrends' => $data['monthlyTrends'],
            'recentSamples' => $data['recentSamples'],
        ]);
    }
}
