import { render, screen } from "@testing-library/react";
import userEvent from "@testing-library/user-event";
import { describe, it, expect, vi } from "vitest";
import InsightsView from "./index";
import type {
	InsightsMonthlyTrend,
	InsightsPatternBreakdown,
	InsightsPopularityPatternCell,
	InsightsPopularityReturn,
	InsightsRecentSample,
	InsightsSummary,
	InsightsViewProps,
} from "./types";

vi.mock("@inertiajs/react", () => ({
	Link: ({
		href,
		children,
	}: {
		href: string;
		children: React.ReactNode;
	}) => <a href={href}>{children}</a>,
}));

vi.mock("@/components/shadcn/ui/select", () => ({
	Select: ({
		onValueChange,
		children,
	}: {
		onValueChange: (v: string) => void;
		children: React.ReactNode;
	}) => (
		<div>
			<button onClick={() => onValueChange("3m")} type="button">
				期間選択
			</button>
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

const dummySummary: InsightsSummary = {
	total_tickets: 42,
	total_purchase_amount: 42000,
	total_payout_amount: 35400,
	return_rate: 84.3,
	hit_rate: 19.0,
};

const dummyPatternBreakdown: InsightsPatternBreakdown[] = [
	{ pattern: "hit", count: 8, ratio: 19.0 },
	{ pattern: "axis_only", count: 6, ratio: 14.3 },
	{ pattern: "others_only", count: 16, ratio: 38.1 },
	{ pattern: "miss", count: 12, ratio: 28.6 },
];

const dummyPopularityPatternMatrix: InsightsPopularityPatternCell[] = [
	{ popularity: "top", pattern: "hit", count: 6, ratio: 30.0 },
	{ popularity: "top", pattern: "axis_only", count: 4, ratio: 20.0 },
	{ popularity: "top", pattern: "others_only", count: 6, ratio: 30.0 },
	{ popularity: "top", pattern: "miss", count: 4, ratio: 20.0 },
	{ popularity: "mid", pattern: "hit", count: 2, ratio: 13.3 },
	{ popularity: "mid", pattern: "axis_only", count: 2, ratio: 13.3 },
	{ popularity: "mid", pattern: "others_only", count: 7, ratio: 46.7 },
	{ popularity: "mid", pattern: "miss", count: 4, ratio: 26.7 },
	{ popularity: "low", pattern: "hit", count: 0, ratio: 0.0 },
	{ popularity: "low", pattern: "axis_only", count: 0, ratio: 0.0 },
	{ popularity: "low", pattern: "others_only", count: 3, ratio: 42.9 },
	{ popularity: "low", pattern: "miss", count: 4, ratio: 57.1 },
];

const dummyPopularityReturns: InsightsPopularityReturn[] = [
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

const dummyMonthlyTrends: InsightsMonthlyTrend[] = [
	{ month: "2026-04", count: 16, hit_rate: 18.8, return_rate: 110.0 },
	{ month: "2026-03", count: 14, hit_rate: 21.4, return_rate: 75.0 },
];

const dummyRecentSamples: InsightsRecentSample[] = [
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
];

const baseProps: InsightsViewProps = {
	period: "1m",
	onPeriodChange: vi.fn(),
	summary: dummySummary,
	patternBreakdown: dummyPatternBreakdown,
	popularityPatternMatrix: dummyPopularityPatternMatrix,
	popularityReturns: dummyPopularityReturns,
	monthlyTrends: dummyMonthlyTrends,
	recentSamples: dummyRecentSamples,
};

describe("InsightsView", () => {
	describe("空状態", () => {
		it("summary=null のとき「該当する馬連流しの記録がありません」が表示される", () => {
			// Arrange
			const props: InsightsViewProps = {
				...baseProps,
				summary: null,
				patternBreakdown: [],
				popularityPatternMatrix: [],
				popularityReturns: [],
				monthlyTrends: [],
				recentSamples: [],
			};

			// Act
			render(<InsightsView {...props} />);

			// Assert
			expect(
				screen.getByText("該当する馬連流しの記録がありません"),
			).toBeInTheDocument();
		});

		it("summary=null のとき「サマリー」「当落パターン分解」「軸馬の人気帯 × 当落パターン」セクションが表示されない", () => {
			// Arrange
			const props: InsightsViewProps = {
				...baseProps,
				summary: null,
				patternBreakdown: [],
				popularityPatternMatrix: [],
				popularityReturns: [],
				monthlyTrends: [],
				recentSamples: [],
			};

			// Act
			render(<InsightsView {...props} />);

			// Assert
			expect(screen.queryByText("サマリー")).not.toBeInTheDocument();
			expect(screen.queryByText("当落パターン分解")).not.toBeInTheDocument();
			expect(
				screen.queryByText("軸馬の人気帯 × 当落パターン"),
			).not.toBeInTheDocument();
		});
	});

	describe("サマリーセクション", () => {
		it("対象件数が `42件` 形式で表示される", () => {
			// Act
			render(<InsightsView {...baseProps} />);

			// Assert
			expect(screen.getByText("42件")).toBeInTheDocument();
		});

		it("総購入額が `¥42,000` 形式で表示される", () => {
			// Act
			render(<InsightsView {...baseProps} />);

			// Assert
			expect(screen.getByText("¥42,000")).toBeInTheDocument();
		});

		it("総払戻額が `¥35,400` 形式で表示される", () => {
			// Act
			render(<InsightsView {...baseProps} />);

			// Assert
			expect(screen.getByText("¥35,400")).toBeInTheDocument();
		});

		it("回収率が `84.3%` 形式で表示される", () => {
			// Act
			render(<InsightsView {...baseProps} />);

			// Assert
			expect(screen.getByText("84.3%")).toBeInTheDocument();
		});

		it("的中率が `19.0%` 形式で表示される", () => {
			// Act
			render(<InsightsView {...baseProps} />);

			// Assert
			expect(screen.getByText("19.0%")).toBeInTheDocument();
		});

		it("回収率 >= 100 のとき回収率に text-green-600 が適用される", () => {
			// Arrange
			const props: InsightsViewProps = {
				...baseProps,
				summary: { ...dummySummary, return_rate: 125.5 },
			};

			// Act
			render(<InsightsView {...props} />);

			// Assert
			const returnRateCell = screen.getByText("125.5%");
			expect(returnRateCell).toHaveClass("text-green-600");
		});

		it("回収率 < 100 のとき回収率に text-red-600 が適用される", () => {
			// Act
			render(<InsightsView {...baseProps} />); // return_rate: 84.3

			// Assert
			const returnRateCell = screen.getByText("84.3%");
			expect(returnRateCell).toHaveClass("text-red-600");
		});
	});

	describe("当落パターン分解", () => {
		it("4パターンのラベルが全て表示される", () => {
			// Act
			render(<InsightsView {...baseProps} />);

			// Assert
			expect(screen.getAllByText("的中").length).toBeGreaterThanOrEqual(1);
			expect(
				screen.getAllByText("軸だけが1〜2着").length,
			).toBeGreaterThanOrEqual(1);
			expect(
				screen.getAllByText("相手だけが1〜2着").length,
			).toBeGreaterThanOrEqual(1);
			expect(screen.getAllByText("外れ").length).toBeGreaterThanOrEqual(1);
		});
	});

	describe("月別の予想精度推移", () => {
		it("monthlyTrends=[] のとき「月別の記録がありません」が表示される", () => {
			// Arrange
			const props: InsightsViewProps = {
				...baseProps,
				monthlyTrends: [],
			};

			// Act
			render(<InsightsView {...props} />);

			// Assert
			expect(screen.getByText("月別の記録がありません")).toBeInTheDocument();
		});

		it("データがあるとき月の値が `YYYY/MM` 形式で表示される", () => {
			// Act
			render(<InsightsView {...baseProps} />);

			// Assert
			expect(screen.getByText("2026/04")).toBeInTheDocument();
		});

		it("行の回収率 >= 100 に text-green-600 が適用される", () => {
			// Act
			render(<InsightsView {...baseProps} />); // 2026-04 は return_rate: 110.0

			// Assert
			const returnRateCell = screen.getByText("110.0%");
			expect(returnRateCell).toHaveClass("text-green-600");
		});

		it("行の回収率 < 100 に text-red-600 が適用される", () => {
			// Act
			render(<InsightsView {...baseProps} />); // 2026-03 は return_rate: 75.0

			// Assert
			const returnRateCell = screen.getByText("75.0%");
			expect(returnRateCell).toHaveClass("text-red-600");
		});
	});

	describe("直近サンプル一覧", () => {
		it("recentSamples=[] のとき「該当するサンプルがありません」が表示される", () => {
			// Arrange
			const props: InsightsViewProps = {
				...baseProps,
				recentSamples: [],
			};

			// Act
			render(<InsightsView {...props} />);

			// Assert
			expect(
				screen.getByText("該当するサンプルがありません"),
			).toBeInTheDocument();
		});

		it("日付が YYYY/MM/DD 形式で表示される", () => {
			// Act
			render(<InsightsView {...baseProps} />);

			// Assert
			expect(screen.getByText("2026/04/28")).toBeInTheDocument();
		});

		it("日付セルのリンクの href が /races/{race_uid} になっている", () => {
			// Act
			render(<InsightsView {...baseProps} />);

			// Assert
			const link = screen.getByRole("link", { name: "2026/04/28" });
			expect(link).toHaveAttribute("href", "/races/abc123");
		});
	});

	describe("期間フィルタ", () => {
		it("期間を変更すると onPeriodChange が InsightsPeriod 型の値で呼ばれる", async () => {
			// Arrange
			const onPeriodChange = vi.fn();
			const user = userEvent.setup();

			// Act
			render(
				<InsightsView {...baseProps} onPeriodChange={onPeriodChange} />,
			);
			await user.click(screen.getByRole("button", { name: "期間選択" }));

			// Assert
			expect(onPeriodChange).toHaveBeenCalledWith("3m");
		});
	});
});
