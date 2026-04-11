<?php

namespace App\UseCases\Balance;

use Illuminate\Support\Facades\DB;

/**
 * 収支ダッシュボードに表示する年間サマリー・日次収支・年一覧を集計して返す。
 */
class ShowDashboardAction
{
    /**
     * @return array{
     *   selected_year: int,
     *   available_years: list<int>,
     *   summary: array{
     *     year: int,
     *     total_purchase_amount: int,
     *     total_payout_amount: int,
     *     total_net_amount: int,
     *     total_return_rate: float,
     *   }|null,
     *   daily_balances: list<array{
     *     date: string,
     *     purchase_amount: int,
     *     payout_amount: int,
     *     net_amount: int,
     *     return_rate: float,
     *   }>,
     * }
     */
    public function execute(int $userId, ?int $year = null): array
    {
        $selectedYear = $year ?? now()->year;

        $availableYears = DB::table('ticket_purchases')
            ->join('races', 'ticket_purchases.race_id', '=', 'races.id')
            ->where('ticket_purchases.user_id', $userId)
            ->selectRaw('DISTINCT YEAR(races.race_date) as year')
            ->orderByDesc('year')
            ->pluck('year')
            ->map(fn ($value) => (int) $value)
            ->values()
            ->all();

        $dailyRows = DB::table('ticket_purchases')
            ->join('races', 'ticket_purchases.race_id', '=', 'races.id')
            ->where('ticket_purchases.user_id', $userId)
            ->whereRaw('YEAR(races.race_date) = ?', [$selectedYear])
            ->groupBy('races.race_date')
            ->orderBy('races.race_date')
            ->selectRaw('DATE_FORMAT(races.race_date, "%Y-%m-%d") as date')
            ->selectRaw('SUM(COALESCE(ticket_purchases.amount, 0)) as purchase_amount')
            ->selectRaw('SUM(COALESCE(ticket_purchases.payout_amount, 0)) as payout_amount')
            ->get();

        $dailyBalances = $dailyRows->map(function ($row) {
            $purchaseAmount = (int) $row->purchase_amount;
            $payoutAmount = (int) $row->payout_amount;
            $netAmount = $payoutAmount - $purchaseAmount;
            $returnRate = $purchaseAmount > 0
                ? round($payoutAmount / $purchaseAmount * 100, 1)
                : 0.0;

            return [
                'date' => (string) $row->date,
                'purchase_amount' => $purchaseAmount,
                'payout_amount' => $payoutAmount,
                'net_amount' => $netAmount,
                'return_rate' => $returnRate,
            ];
        })->values()->all();

        $summary = null;
        if (! empty($dailyBalances)) {
            $totalPurchaseAmount = array_sum(array_column($dailyBalances, 'purchase_amount'));
            $totalPayoutAmount = array_sum(array_column($dailyBalances, 'payout_amount'));
            $totalNetAmount = $totalPayoutAmount - $totalPurchaseAmount;
            $totalReturnRate = $totalPurchaseAmount > 0
                ? round($totalPayoutAmount / $totalPurchaseAmount * 100, 1)
                : 0.0;

            $summary = [
                'year' => $selectedYear,
                'total_purchase_amount' => (int) $totalPurchaseAmount,
                'total_payout_amount' => (int) $totalPayoutAmount,
                'total_net_amount' => (int) $totalNetAmount,
                'total_return_rate' => (float) $totalReturnRate,
            ];
        }

        return [
            'selected_year' => $selectedYear,
            'available_years' => $availableYears,
            'summary' => $summary,
            'daily_balances' => $dailyBalances,
        ];
    }
}
