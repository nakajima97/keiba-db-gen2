<?php

namespace App\UseCases\Balance;

use App\UseCases\TicketPurchase\ExpandSelectionsAction;
use Illuminate\Support\Facades\DB;

/**
 * 収支ダッシュボードに表示する年間サマリー・日次収支・年一覧を集計して返す。
 *
 * 購入金額は「単価 × 有効点数」で算出する。有効点数は selections を
 * ExpandSelectionsAction で展開した結果の件数。
 */
class ShowDashboardAction
{
    public function __construct(
        private ExpandSelectionsAction $expandSelections,
    ) {}

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

        $purchaseRows = DB::table('ticket_purchases')
            ->join('races', 'ticket_purchases.race_id', '=', 'races.id')
            ->join('ticket_types', 'ticket_purchases.ticket_type_id', '=', 'ticket_types.id')
            ->join('buy_types', 'ticket_purchases.buy_type_id', '=', 'buy_types.id')
            ->where('ticket_purchases.user_id', $userId)
            ->whereRaw('YEAR(races.race_date) = ?', [$selectedYear])
            ->orderBy('races.race_date')
            ->selectRaw('DATE_FORMAT(races.race_date, "%Y-%m-%d") as date')
            ->selectRaw('ticket_purchases.unit_stake as unit_stake')
            ->selectRaw('COALESCE(ticket_purchases.payout_amount, 0) as payout_amount')
            ->selectRaw('ticket_purchases.selections as selections')
            ->selectRaw('ticket_types.name as ticket_type_name')
            ->selectRaw('buy_types.name as buy_type_name')
            ->get();

        /** @var array<string, array{purchase_amount: int, payout_amount: int}> $dailyMap */
        $dailyMap = [];
        foreach ($purchaseRows as $row) {
            $date = (string) $row->date;
            $selections = json_decode((string) $row->selections, true);

            $purchaseAmount = $row->unit_stake !== null
                ? (int) $row->unit_stake * count($this->expandSelections->execute(
                    (string) $row->ticket_type_name,
                    (string) $row->buy_type_name,
                    is_array($selections) ? $selections : null,
                ))
                : 0;

            if (! isset($dailyMap[$date])) {
                $dailyMap[$date] = ['purchase_amount' => 0, 'payout_amount' => 0];
            }

            $dailyMap[$date]['purchase_amount'] += $purchaseAmount;
            $dailyMap[$date]['payout_amount'] += (int) $row->payout_amount;
        }

        krsort($dailyMap);

        $dailyBalances = [];
        foreach ($dailyMap as $date => $amounts) {
            $purchaseAmount = $amounts['purchase_amount'];
            $payoutAmount = $amounts['payout_amount'];
            $netAmount = $payoutAmount - $purchaseAmount;
            $returnRate = $purchaseAmount > 0
                ? round($payoutAmount / $purchaseAmount * 100, 1)
                : 0.0;

            $dailyBalances[] = [
                'date' => $date,
                'purchase_amount' => $purchaseAmount,
                'payout_amount' => $payoutAmount,
                'net_amount' => $netAmount,
                'return_rate' => $returnRate,
            ];
        }

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
