export type DailyBalance = {
	date: string; // "2026-04-05" 形式
	purchase_amount: number;
	payout_amount: number;
	net_amount: number; // payout_amount - purchase_amount
	return_rate: number; // payout_amount / purchase_amount * 100
};

export type YearlySummary = {
	year: number;
	total_purchase_amount: number;
	total_payout_amount: number;
	total_net_amount: number;
	total_return_rate: number;
};

export type BalanceDashboardProps = {
	selectedYear: number;
	availableYears: number[];
	summary: YearlySummary | null;
	dailyBalances: DailyBalance[];
	onYearChange: (year: number) => void;
};
