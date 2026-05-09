import type { Meta, StoryObj } from "@storybook/react-vite";
import BalanceDashboard from ".";
import type { BalanceDashboardProps } from ".";

const meta: Meta<typeof BalanceDashboard> = {
	title: "features/dashboard/presentational/BalanceDashboard",
	component: BalanceDashboard,
};

export default meta;
type Story = StoryObj<typeof BalanceDashboard>;

const baseArgs: Pick<
	BalanceDashboardProps,
	"selectedYear" | "availableYears" | "onYearChange" | "hasMore" | "isLoadingMore" | "onLoadMore"
> = {
	selectedYear: 2026,
	availableYears: [2024, 2025, 2026],
	onYearChange: () => {},
	hasMore: false,
	isLoadingMore: false,
	onLoadMore: () => {},
};

const sampleDailyBalances: BalanceDashboardProps["dailyBalances"] = [
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

const manyDailyBalances: BalanceDashboardProps["dailyBalances"] = [
	{ date: "2026-01-04", purchase_amount: 3000, payout_amount: 4500, net_amount: 1500, return_rate: 150.0 },
	{ date: "2026-01-11", purchase_amount: 5000, payout_amount: 3000, net_amount: -2000, return_rate: 60.0 },
	{ date: "2026-01-18", purchase_amount: 8000, payout_amount: 9600, net_amount: 1600, return_rate: 120.0 },
	{ date: "2026-01-25", purchase_amount: 4000, payout_amount: 3200, net_amount: -800, return_rate: 80.0 },
	{ date: "2026-02-01", purchase_amount: 6000, payout_amount: 7800, net_amount: 1800, return_rate: 130.0 },
	{ date: "2026-02-08", purchase_amount: 2000, payout_amount: 1400, net_amount: -600, return_rate: 70.0 },
	{ date: "2026-02-15", purchase_amount: 10000, payout_amount: 12000, net_amount: 2000, return_rate: 120.0 },
	{ date: "2026-02-22", purchase_amount: 5000, payout_amount: 4000, net_amount: -1000, return_rate: 80.0 },
	{ date: "2026-03-01", purchase_amount: 3000, payout_amount: 5100, net_amount: 2100, return_rate: 170.0 },
	{ date: "2026-03-08", purchase_amount: 7000, payout_amount: 5600, net_amount: -1400, return_rate: 80.0 },
	{ date: "2026-03-15", purchase_amount: 4000, payout_amount: 4800, net_amount: 800, return_rate: 120.0 },
	{ date: "2026-03-22", purchase_amount: 6000, payout_amount: 4200, net_amount: -1800, return_rate: 70.0 },
	{ date: "2026-03-29", purchase_amount: 2000, payout_amount: 3000, net_amount: 1000, return_rate: 150.0 },
	{ date: "2026-04-05", purchase_amount: 3000, payout_amount: 5000, net_amount: 2000, return_rate: 166.7 },
	{ date: "2026-04-06", purchase_amount: 5000, payout_amount: 2000, net_amount: -3000, return_rate: 40.0 },
	{ date: "2026-04-12", purchase_amount: 10000, payout_amount: 9500, net_amount: -500, return_rate: 95.0 },
	{ date: "2026-04-13", purchase_amount: 2000, payout_amount: 4800, net_amount: 2800, return_rate: 240.0 },
	{ date: "2026-04-19", purchase_amount: 8000, payout_amount: 6400, net_amount: -1600, return_rate: 80.0 },
	{ date: "2026-04-20", purchase_amount: 4000, payout_amount: 5600, net_amount: 1600, return_rate: 140.0 },
	{ date: "2026-04-26", purchase_amount: 6000, payout_amount: 4800, net_amount: -1200, return_rate: 80.0 },
	{ date: "2026-04-27", purchase_amount: 3000, payout_amount: 4500, net_amount: 1500, return_rate: 150.0 },
	{ date: "2026-05-03", purchase_amount: 5000, payout_amount: 3500, net_amount: -1500, return_rate: 70.0 },
	{ date: "2026-05-04", purchase_amount: 7000, payout_amount: 9100, net_amount: 2100, return_rate: 130.0 },
	{ date: "2026-05-05", purchase_amount: 10000, payout_amount: 8000, net_amount: -2000, return_rate: 80.0 },
	{ date: "2026-05-10", purchase_amount: 2000, payout_amount: 3400, net_amount: 1400, return_rate: 170.0 },
	{ date: "2026-05-11", purchase_amount: 4000, payout_amount: 2800, net_amount: -1200, return_rate: 70.0 },
	{ date: "2026-05-17", purchase_amount: 6000, payout_amount: 7800, net_amount: 1800, return_rate: 130.0 },
	{ date: "2026-05-18", purchase_amount: 3000, payout_amount: 2100, net_amount: -900, return_rate: 70.0 },
	{ date: "2026-05-24", purchase_amount: 5000, payout_amount: 6500, net_amount: 1500, return_rate: 130.0 },
	{ date: "2026-05-25", purchase_amount: 8000, payout_amount: 6400, net_amount: -1600, return_rate: 80.0 },
];

export const Empty: Story = {
	name: "記録なし",
	args: {
		...baseArgs,
		summary: null,
		dailyBalances: [],
	},
};

export const WithData: Story = {
	name: "データあり（プラス・マイナス混在）",
	args: {
		...baseArgs,
		summary: {
			year: 2026,
			total_purchase_amount: 20000,
			total_payout_amount: 21300,
			total_net_amount: 1300,
			total_return_rate: 106.5,
		},
		dailyBalances: sampleDailyBalances,
	},
};

export const WithNegativeYear: Story = {
	name: "年間マイナスのサマリー",
	args: {
		...baseArgs,
		summary: {
			year: 2026,
			total_purchase_amount: 50000,
			total_payout_amount: 45000,
			total_net_amount: -5000,
			total_return_rate: 90.0,
		},
		dailyBalances: sampleDailyBalances,
	},
};

export const MobileWithData: Story = {
	name: "データあり（モバイル）",
	globals: {
		viewport: { value: "mobile1", isRotated: false },
	},
	args: {
		...baseArgs,
		summary: {
			year: 2026,
			total_purchase_amount: 20000,
			total_payout_amount: 21300,
			total_net_amount: 1300,
			total_return_rate: 106.5,
		},
		dailyBalances: sampleDailyBalances,
	},
};

export const Default: Story = {
	name: "全件表示済み（もっと読み込むなし）",
	args: {
		...baseArgs,
		hasMore: false,
		isLoadingMore: false,
		summary: {
			year: 2026,
			total_purchase_amount: 161000,
			total_payout_amount: 156700,
			total_net_amount: -4300,
			total_return_rate: 97.3,
		},
		dailyBalances: manyDailyBalances,
	},
};

export const HasMore: Story = {
	name: "もっと読み込むボタン表示",
	args: {
		...baseArgs,
		hasMore: true,
		isLoadingMore: false,
		summary: {
			year: 2026,
			total_purchase_amount: 161000,
			total_payout_amount: 156700,
			total_net_amount: -4300,
			total_return_rate: 97.3,
		},
		dailyBalances: manyDailyBalances,
	},
};

export const LoadingMore: Story = {
	name: "追加読み込み中（Spinner表示）",
	args: {
		...baseArgs,
		hasMore: true,
		isLoadingMore: true,
		summary: {
			year: 2026,
			total_purchase_amount: 161000,
			total_payout_amount: 156700,
			total_net_amount: -4300,
			total_return_rate: 97.3,
		},
		dailyBalances: manyDailyBalances,
	},
};
