import { Head, router, usePage } from "@inertiajs/react";
import InsightsView from "@/features/insights/components/InsightsView";
import type {
	InsightsMonthlyTrend,
	InsightsPatternBreakdown,
	InsightsPeriod,
	InsightsPopularityPatternCell,
	InsightsPopularityReturn,
	InsightsRecentSample,
	InsightsSummary,
} from "@/features/insights/components/InsightsView";
import { insights } from "@/routes";

type InsightsPageProps = {
	period: InsightsPeriod;
	summary: InsightsSummary | null;
	patternBreakdown: InsightsPatternBreakdown[];
	popularityPatternMatrix: InsightsPopularityPatternCell[];
	popularityReturns: InsightsPopularityReturn[];
	monthlyTrends: InsightsMonthlyTrend[];
	recentSamples: InsightsRecentSample[];
};

const Insights = () => {
	const {
		period,
		summary,
		patternBreakdown,
		popularityPatternMatrix,
		popularityReturns,
		monthlyTrends,
		recentSamples,
	} = usePage<InsightsPageProps>().props;

	const handlePeriodChange = (next: InsightsPeriod) => {
		router.get(
			insights.url(),
			{ period: next },
			{ preserveState: false, preserveScroll: true },
		);
	};

	return (
		<>
			<Head title="振り返り" />
			<InsightsView
				period={period}
				onPeriodChange={handlePeriodChange}
				summary={summary}
				patternBreakdown={patternBreakdown}
				popularityPatternMatrix={popularityPatternMatrix}
				popularityReturns={popularityReturns}
				monthlyTrends={monthlyTrends}
				recentSamples={recentSamples}
			/>
		</>
	);
};

export default Insights;

Insights.layout = {
	breadcrumbs: [
		{
			title: "Insights",
			href: insights(),
		},
	],
};
