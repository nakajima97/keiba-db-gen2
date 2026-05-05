import type { Meta, StoryObj } from "@storybook/react-vite";
import InsightsView from ".";
import type { InsightsViewProps } from ".";

const meta: Meta<typeof InsightsView> = {
	title: "features/insights/components/InsightsView",
	component: InsightsView,
};

export default meta;
type Story = StoryObj<typeof InsightsView>;

const baseArgs: Pick<InsightsViewProps, "onPeriodChange"> = {
	onPeriodChange: () => {},
};

const sampleSummary: InsightsViewProps["summary"] = {
	total_tickets: 42,
	total_purchase_amount: 42000,
	total_payout_amount: 35400,
	return_rate: 84.3,
	hit_rate: 19.0,
};

// 4パターンの合計件数 = 42 (8 + 6 + 16 + 12)
const samplePatternBreakdown: InsightsViewProps["patternBreakdown"] = [
	{ pattern: "hit", count: 8, ratio: 19.0 },
	{ pattern: "axis_only", count: 6, ratio: 14.3 },
	{ pattern: "others_only", count: 16, ratio: 38.1 },
	{ pattern: "miss", count: 12, ratio: 28.6 },
];

// 人気帯 × 4パターンのクロス集計（同人気帯内の合計が100%になる想定）
const samplePopularityPatternMatrix: InsightsViewProps["popularityPatternMatrix"] =
	[
		// 1〜3番人気: 計20件
		{ popularity: "top", pattern: "hit", count: 6, ratio: 30.0 },
		{ popularity: "top", pattern: "axis_only", count: 4, ratio: 20.0 },
		{ popularity: "top", pattern: "others_only", count: 6, ratio: 30.0 },
		{ popularity: "top", pattern: "miss", count: 4, ratio: 20.0 },
		// 4〜6番人気: 計15件
		{ popularity: "mid", pattern: "hit", count: 2, ratio: 13.3 },
		{ popularity: "mid", pattern: "axis_only", count: 2, ratio: 13.3 },
		{ popularity: "mid", pattern: "others_only", count: 7, ratio: 46.7 },
		{ popularity: "mid", pattern: "miss", count: 4, ratio: 26.7 },
		// 7番人気以下: 計7件
		{ popularity: "low", pattern: "hit", count: 0, ratio: 0.0 },
		{ popularity: "low", pattern: "axis_only", count: 0, ratio: 0.0 },
		{ popularity: "low", pattern: "others_only", count: 3, ratio: 42.9 },
		{ popularity: "low", pattern: "miss", count: 4, ratio: 57.1 },
	];

const samplePopularityReturns: InsightsViewProps["popularityReturns"] = [
	{
		popularity: "top",
		count: 20,
		purchase_amount: 20000,
		payout_amount: 24000,
		return_rate: 120.0,
	},
	{
		popularity: "mid",
		count: 15,
		purchase_amount: 15000,
		payout_amount: 9300,
		return_rate: 62.0,
	},
	{
		popularity: "low",
		count: 7,
		purchase_amount: 7000,
		payout_amount: 2100,
		return_rate: 30.0,
	},
];

const sampleMonthlyTrends: InsightsViewProps["monthlyTrends"] = [
	{ month: "2026-02", count: 12, hit_rate: 16.7, return_rate: 75.0 },
	{ month: "2026-03", count: 14, hit_rate: 21.4, return_rate: 92.0 },
	{ month: "2026-04", count: 16, hit_rate: 18.8, return_rate: 85.0 },
];

const sampleRecentSamples: InsightsViewProps["recentSamples"] = [
	{
		ticket_id: 101,
		race_uid: "abc123",
		race_date: "2026-04-28",
		venue_name: "東京",
		race_number: 11,
		axis_horse_numbers: [3],
		axis_best_finishing_order: 1,
		others_best_finishing_order: 2,
		pattern: "hit",
		purchase_amount: 1000,
		payout_amount: 4200,
	},
	{
		ticket_id: 102,
		race_uid: "abc124",
		race_date: "2026-04-28",
		venue_name: "東京",
		race_number: 12,
		axis_horse_numbers: [5],
		axis_best_finishing_order: 1,
		others_best_finishing_order: 5,
		pattern: "axis_only",
		purchase_amount: 1000,
		payout_amount: 0,
	},
	{
		ticket_id: 103,
		race_uid: "def456",
		race_date: "2026-04-27",
		venue_name: "京都",
		race_number: 10,
		axis_horse_numbers: [2],
		axis_best_finishing_order: 6,
		others_best_finishing_order: 1,
		pattern: "others_only",
		purchase_amount: 1000,
		payout_amount: 0,
	},
	{
		ticket_id: 104,
		race_uid: "def457",
		race_date: "2026-04-27",
		venue_name: "京都",
		race_number: 11,
		axis_horse_numbers: [1],
		axis_best_finishing_order: 8,
		others_best_finishing_order: 7,
		pattern: "miss",
		purchase_amount: 1000,
		payout_amount: 0,
	},
	{
		ticket_id: 105,
		race_uid: "ghi789",
		race_date: "2026-04-26",
		venue_name: "中山",
		race_number: 9,
		axis_horse_numbers: [4],
		axis_best_finishing_order: 2,
		others_best_finishing_order: 1,
		pattern: "hit",
		purchase_amount: 1500,
		payout_amount: 6300,
	},
	{
		ticket_id: 106,
		race_uid: "ghi790",
		race_date: "2026-04-26",
		venue_name: "中山",
		race_number: 10,
		axis_horse_numbers: [7],
		axis_best_finishing_order: 4,
		others_best_finishing_order: 2,
		pattern: "others_only",
		purchase_amount: 1000,
		payout_amount: 0,
	},
	{
		ticket_id: 107,
		race_uid: "jkl111",
		race_date: "2026-04-21",
		venue_name: "阪神",
		race_number: 11,
		axis_horse_numbers: [2],
		axis_best_finishing_order: 1,
		others_best_finishing_order: 4,
		pattern: "axis_only",
		purchase_amount: 1000,
		payout_amount: 0,
	},
	{
		ticket_id: 108,
		race_uid: "jkl112",
		race_date: "2026-04-21",
		venue_name: "阪神",
		race_number: 12,
		axis_horse_numbers: [6],
		axis_best_finishing_order: 9,
		others_best_finishing_order: 12,
		pattern: "miss",
		purchase_amount: 1000,
		payout_amount: 0,
	},
	{
		ticket_id: 109,
		race_uid: "mno222",
		race_date: "2026-04-20",
		venue_name: "新潟",
		race_number: 11,
		axis_horse_numbers: [1, 3],
		axis_best_finishing_order: 1,
		others_best_finishing_order: 2,
		pattern: "hit",
		purchase_amount: 2000,
		payout_amount: 8400,
	},
	{
		ticket_id: 110,
		race_uid: "mno223",
		race_date: "2026-04-20",
		venue_name: "新潟",
		race_number: 12,
		axis_horse_numbers: [8],
		axis_best_finishing_order: 5,
		others_best_finishing_order: 3,
		pattern: "others_only",
		purchase_amount: 1500,
		payout_amount: 0,
	},
];

export const Recent1Month: Story = {
	name: "直近1ヶ月（データあり）",
	args: {
		...baseArgs,
		period: "1m",
		summary: sampleSummary,
		patternBreakdown: samplePatternBreakdown,
		popularityPatternMatrix: samplePopularityPatternMatrix,
		popularityReturns: samplePopularityReturns,
		monthlyTrends: sampleMonthlyTrends,
		recentSamples: sampleRecentSamples,
	},
};

export const AllPeriod: Story = {
	name: "全期間（豊富なデータ）",
	args: {
		...baseArgs,
		period: "all",
		summary: {
			total_tickets: 320,
			total_purchase_amount: 320000,
			total_payout_amount: 352000,
			return_rate: 110.0,
			hit_rate: 22.5,
		},
		patternBreakdown: [
			{ pattern: "hit", count: 72, ratio: 22.5 },
			{ pattern: "axis_only", count: 48, ratio: 15.0 },
			{ pattern: "others_only", count: 112, ratio: 35.0 },
			{ pattern: "miss", count: 88, ratio: 27.5 },
		],
		popularityPatternMatrix: [
			{ popularity: "top", pattern: "hit", count: 50, ratio: 31.3 },
			{ popularity: "top", pattern: "axis_only", count: 30, ratio: 18.8 },
			{ popularity: "top", pattern: "others_only", count: 50, ratio: 31.3 },
			{ popularity: "top", pattern: "miss", count: 30, ratio: 18.8 },
			{ popularity: "mid", pattern: "hit", count: 18, ratio: 15.0 },
			{ popularity: "mid", pattern: "axis_only", count: 14, ratio: 11.7 },
			{ popularity: "mid", pattern: "others_only", count: 50, ratio: 41.7 },
			{ popularity: "mid", pattern: "miss", count: 38, ratio: 31.7 },
			{ popularity: "low", pattern: "hit", count: 4, ratio: 10.0 },
			{ popularity: "low", pattern: "axis_only", count: 4, ratio: 10.0 },
			{ popularity: "low", pattern: "others_only", count: 12, ratio: 30.0 },
			{ popularity: "low", pattern: "miss", count: 20, ratio: 50.0 },
		],
		popularityReturns: [
			{
				popularity: "top",
				count: 160,
				purchase_amount: 160000,
				payout_amount: 200000,
				return_rate: 125.0,
			},
			{
				popularity: "mid",
				count: 120,
				purchase_amount: 120000,
				payout_amount: 120000,
				return_rate: 100.0,
			},
			{
				popularity: "low",
				count: 40,
				purchase_amount: 40000,
				payout_amount: 32000,
				return_rate: 80.0,
			},
		],
		monthlyTrends: [
			{ month: "2025-11", count: 22, hit_rate: 18.2, return_rate: 95.0 },
			{ month: "2025-12", count: 28, hit_rate: 21.4, return_rate: 105.0 },
			{ month: "2026-01", count: 30, hit_rate: 23.3, return_rate: 112.0 },
			{ month: "2026-02", count: 26, hit_rate: 19.2, return_rate: 88.0 },
			{ month: "2026-03", count: 30, hit_rate: 26.7, return_rate: 130.0 },
			{ month: "2026-04", count: 28, hit_rate: 21.4, return_rate: 108.0 },
		],
		recentSamples: sampleRecentSamples,
	},
};

export const Empty: Story = {
	name: "データ0件",
	args: {
		...baseArgs,
		period: "1m",
		summary: null,
		patternBreakdown: [],
		popularityPatternMatrix: [],
		popularityReturns: [],
		monthlyTrends: [],
		recentSamples: [],
	},
};

export const MobileWithData: Story = {
	name: "直近1ヶ月（モバイル）",
	globals: {
		viewport: { value: "mobile1", isRotated: false },
	},
	args: {
		...baseArgs,
		period: "1m",
		summary: sampleSummary,
		patternBreakdown: samplePatternBreakdown,
		popularityPatternMatrix: samplePopularityPatternMatrix,
		popularityReturns: samplePopularityReturns,
		monthlyTrends: sampleMonthlyTrends,
		recentSamples: sampleRecentSamples,
	},
};
