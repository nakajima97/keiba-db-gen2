import { render, screen } from "@testing-library/react";
import { describe, it, expect, vi } from "vitest";
import BalanceDashboard from "./index";
import type {
	BalanceDashboardProps,
	DailyBalance,
	YearlySummary,
} from "./types";

vi.mock("@/components/shadcn/ui/select", () => ({
	Select: ({
		onValueChange,
		children,
	}: {
		onValueChange: (v: string) => void;
		children: React.ReactNode;
	}) => (
		<div>
			<button onClick={() => onValueChange("2025")}>年選択</button>
			{children}
		</div>
	),
	SelectTrigger: ({ children }: { children: React.ReactNode }) => (
		<div>{children}</div>
	),
	SelectValue: () => <span />,
	SelectContent: ({ children }: { children: React.ReactNode }) => (
		<div>{children}</div>
	),
	SelectItem: ({
		value,
		children,
	}: {
		value: string;
		children: React.ReactNode;
	}) => <div data-value={value}>{children}</div>,
}));

const baseProps: BalanceDashboardProps = {
	selectedYear: 2026,
	availableYears: [2025, 2026],
	summary: null,
	dailyBalances: [],
	onYearChange: vi.fn(),
};

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
];

describe("BalanceDashboard", () => {
	describe("レンダリング", () => {
		it("年セレクタが表示される", () => {
			// Act
			render(<BalanceDashboard {...baseProps} />);

			// Assert
			expect(
				screen.getByRole("button", { name: "年選択" }),
			).toBeInTheDocument();
		});

		it("日次テーブルのヘッダーが表示される", () => {
			// Arrange
			const props: BalanceDashboardProps = {
				...baseProps,
				dailyBalances: dummyDailyBalances,
			};

			// Act
			render(<BalanceDashboard {...props} />);

			// Assert
			expect(screen.getByText("日付")).toBeInTheDocument();
			expect(screen.getByText("購入金額")).toBeInTheDocument();
			expect(screen.getByText("払い戻し金額")).toBeInTheDocument();
			expect(
				screen.getAllByText("プラスマイナス").length,
			).toBeGreaterThanOrEqual(1);
			expect(screen.getAllByText("利益率").length).toBeGreaterThanOrEqual(1);
		});
	});

	describe("空状態", () => {
		it("dailyBalances=[] のとき「記録がありません」が表示される", () => {
			// Act
			render(<BalanceDashboard {...baseProps} dailyBalances={[]} />);

			// Assert
			expect(screen.getByText("記録がありません")).toBeInTheDocument();
		});

		it("summary=null のときサマリーカードに「-」が表示される", () => {
			// Act
			render(<BalanceDashboard {...baseProps} summary={null} />);

			// Assert
			expect(screen.getAllByText("-").length).toBeGreaterThanOrEqual(1);
		});
	});

	describe("データ表示", () => {
		it("日付が YYYY/MM/DD 形式で表示される", () => {
			// Arrange
			const props: BalanceDashboardProps = {
				...baseProps,
				dailyBalances: [dummyDailyBalances[0]],
			};

			// Act
			render(<BalanceDashboard {...props} />);

			// Assert
			expect(screen.getByText("2026/04/05")).toBeInTheDocument();
		});

		it("金額が ¥N,NNN 形式で表示される", () => {
			// Arrange
			const props: BalanceDashboardProps = {
				...baseProps,
				summary: dummySummary,
				dailyBalances: [dummyDailyBalances[0]],
			};

			// Act
			render(<BalanceDashboard {...props} />);

			// Assert
			expect(screen.getAllByText("¥3,000").length).toBeGreaterThanOrEqual(1);
		});

		it("net_amount >= 0 の行のプラスマイナスに text-green-600 が適用される", () => {
			// Arrange
			const props: BalanceDashboardProps = {
				...baseProps,
				dailyBalances: [dummyDailyBalances[0]], // net_amount: 2000
			};

			// Act
			render(<BalanceDashboard {...props} />);

			// Assert
			const positiveCell = screen.getByText("+¥2,000");
			expect(positiveCell).toHaveClass("text-green-600");
		});

		it("net_amount < 0 の行のプラスマイナスに text-red-600 が適用される", () => {
			// Arrange
			const props: BalanceDashboardProps = {
				...baseProps,
				dailyBalances: [dummyDailyBalances[1]], // net_amount: -3000
			};

			// Act
			render(<BalanceDashboard {...props} />);

			// Assert
			const negativeCell = screen.getByText("¥-3,000");
			expect(negativeCell).toHaveClass("text-red-600");
		});

		it("return_rate >= 100 の利益率に text-green-600 が適用される", () => {
			// Arrange
			const props: BalanceDashboardProps = {
				...baseProps,
				dailyBalances: [dummyDailyBalances[0]], // return_rate: 166.7
			};

			// Act
			render(<BalanceDashboard {...props} />);

			// Assert
			const returnRateCell = screen.getByText("166.7%");
			expect(returnRateCell).toHaveClass("text-green-600");
		});

		it("return_rate < 100 の利益率に text-red-600 が適用される", () => {
			// Arrange
			const props: BalanceDashboardProps = {
				...baseProps,
				dailyBalances: [dummyDailyBalances[1]], // return_rate: 40.0
			};

			// Act
			render(<BalanceDashboard {...props} />);

			// Assert
			const returnRateCell = screen.getByText("40.0%");
			expect(returnRateCell).toHaveClass("text-red-600");
		});
	});

	describe("インタラクション", () => {
		it("年セレクタを変更すると onYearChange が数値型の年で呼ばれる", async () => {
			// Arrange
			const onYearChange = vi.fn();
			const { default: userEvent } = await import(
				"@testing-library/user-event"
			);
			const user = userEvent.setup();

			// Act
			render(<BalanceDashboard {...baseProps} onYearChange={onYearChange} />);
			await user.click(screen.getByRole("button", { name: "年選択" }));

			// Assert
			expect(onYearChange).toHaveBeenCalledWith(2025);
			expect(typeof onYearChange.mock.calls[0][0]).toBe("number");
		});
	});
});
