import { Head } from "@inertiajs/react";
import { dashboard } from "@/routes";
import BalanceDashboard from "@/features/dashboard/presentational/BalanceDashboard";
import type { DailyBalance, YearlySummary } from "@/features/dashboard/presentational/BalanceDashboard";

const dummySummary: YearlySummary = {
	year: 2026,
	total_purchase_amount: 50000,
	total_payout_amount: 45000,
	total_net_amount: -5000,
	total_return_rate: 90.0,
};

const dummyDailyBalances: DailyBalance[] = [
	{
		date: "2026-04-05",
		purchase_amount: 3000,
		payout_amount: 5000,
		net_amount: 2000,
		return_rate: 166.7,
	},
	{
		date: "2026-04-06",
		purchase_amount: 5000,
		payout_amount: 2000,
		net_amount: -3000,
		return_rate: 40.0,
	},
	{
		date: "2026-04-12",
		purchase_amount: 10000,
		payout_amount: 9500,
		net_amount: -500,
		return_rate: 95.0,
	},
	{
		date: "2026-04-13",
		purchase_amount: 2000,
		payout_amount: 4800,
		net_amount: 2800,
		return_rate: 240.0,
	},
];

export default function Dashboard() {
	return (
		<>
			<Head title="収支ダッシュボード" />
			<BalanceDashboard
				selectedYear={2026}
				availableYears={[2025, 2026]}
				summary={dummySummary}
				dailyBalances={dummyDailyBalances}
				onYearChange={() => {}}
			/>
		</>
	);
}

Dashboard.layout = {
	breadcrumbs: [
		{
			title: "Dashboard",
			href: dashboard(),
		},
	],
};
