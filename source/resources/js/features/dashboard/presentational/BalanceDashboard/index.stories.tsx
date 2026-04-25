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
	"selectedYear" | "availableYears" | "onYearChange"
> = {
	selectedYear: 2026,
	availableYears: [2024, 2025, 2026],
	onYearChange: () => {},
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
