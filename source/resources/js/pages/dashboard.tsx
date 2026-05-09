import { Head, router, usePage } from "@inertiajs/react";
import { useState } from "react";
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
	next_cursor: string | null;
};

const Dashboard = () => {
	const { selected_year, available_years, summary, daily_balances, next_cursor } =
		usePage<DashboardPageProps>().props;

	const [isLoadingMore, setIsLoadingMore] = useState(false);

	const handleYearChange = (year: number) => {
		router.get(
			dashboard.url(),
			{ year },
			{ preserveState: false, preserveScroll: true },
		);
	};

	const handleLoadMore = () => {
		if (next_cursor === null) {
			return;
		}

		setIsLoadingMore(true);
		router.reload({
			data: { cursor: next_cursor },
			only: ["daily_balances", "next_cursor"],
			// @ts-expect-error: Inertia v3 の ReloadOptions 型には含まれないが、サーバ側 Inertia::merge() と組み合わせて daily_balances 配列を追記する意図を明示するため指定する
			merge: ["daily_balances"],
			onFinish: () => setIsLoadingMore(false),
		});
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
				hasMore={next_cursor !== null}
				isLoadingMore={isLoadingMore}
				onLoadMore={handleLoadMore}
			/>
		</>
	);
};

export default Dashboard;

Dashboard.layout = {
	breadcrumbs: [
		{
			title: "Dashboard",
			href: dashboard(),
		},
	],
};
