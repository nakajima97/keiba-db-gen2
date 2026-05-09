import { render, screen } from "@testing-library/react";
import { describe, expect, it, vi } from "vitest";
import { createInertiaReactMock } from "@/tests/mocks";
import Insights from "./insights";

const { routerGet } = vi.hoisted(() => ({ routerGet: vi.fn() }));

vi.mock("@inertiajs/react", () =>
	createInertiaReactMock({
		usePage: () => ({
			props: {
				period: "1m",
				summary: {
					total_tickets: 10,
					total_purchase_amount: 10000,
					total_payout_amount: 8000,
					return_rate: 80.0,
					hit_rate: 20.0,
				},
				patternBreakdown: [
					{ pattern: "hit", count: 2, ratio: 20.0 },
					{ pattern: "axis_only", count: 1, ratio: 10.0 },
					{ pattern: "others_only", count: 4, ratio: 40.0 },
					{ pattern: "miss", count: 3, ratio: 30.0 },
				],
				popularityPatternMatrix: [],
				popularityReturns: [],
				monthlyTrends: [],
				recentSamples: [
					{
						ticket_id: 1,
						race_uid: "uid-1",
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
				],
			},
		}),
		router: { get: routerGet },
	}),
);

vi.mock("@/routes", () => ({
	insights: Object.assign(() => "/insights", {
		url: () => "/insights",
	}),
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
			<button type="button" onClick={() => onValueChange("3m")}>
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

describe("Insights ページ", () => {
	it("ハッピーパス: Inertia propsのデータが InsightsView に表示される", () => {
		// Act
		render(<Insights />);

		// Assert
		expect(document.title).toBe("振り返り");
		expect(screen.getByText("振り返り（馬連流し）")).toBeInTheDocument();
		expect(screen.getByText("10件")).toBeInTheDocument();
		expect(screen.getByText("2026/04/28")).toBeInTheDocument();
	});

	it("期間セレクタを変更すると router.get が選択された期間のクエリパラメータで呼ばれる", async () => {
		// Arrange
		routerGet.mockClear();
		const { default: userEvent } = await import("@testing-library/user-event");
		const user = userEvent.setup();

		// Act
		render(<Insights />);
		await user.click(screen.getByRole("button", { name: "期間選択" }));

		// Assert
		expect(routerGet).toHaveBeenCalledTimes(1);
		const callArgs = routerGet.mock.calls[0];
		const dataArg = callArgs[1] as { period: string };
		expect(dataArg.period).toBe("3m");
	});
});
