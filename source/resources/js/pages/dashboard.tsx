import { Head, router, usePage } from "@inertiajs/react";
import { dashboard } from "@/routes";
import BalanceDashboard from "@/features/dashboard/presentational/BalanceDashboard";
import type {
	DailyBalance,
	YearlySummary,
} from "@/features/dashboard/presentational/BalanceDashboard";

type DashboardPageProps = {
	selected_year: number;
	available_years: number[];
	summary: YearlySummary | null;
	daily_balances: DailyBalance[];
};

export default function Dashboard() {
	const { selected_year, available_years, summary, daily_balances } =
		usePage<DashboardPageProps>().props;

	const handleYearChange = (year: number) => {
		router.get(
			dashboard.url(),
			{ year },
			{ preserveState: false, preserveScroll: true },
		);
	};

	return (
		<>
			<Head title="収支ダッシュボード" />
			<BalanceDashboard
				selectedYear={selected_year}
				availableYears={available_years}
				summary={summary}
				dailyBalances={daily_balances}
				onYearChange={handleYearChange}
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
