import { render, screen } from "@testing-library/react";
import { describe, expect, it, vi } from "vitest";
import { createInertiaReactMock } from "@/tests/mocks";
import Dashboard from "./dashboard";

const { routerGet, routerReload } = vi.hoisted(() => ({
	routerGet: vi.fn(),
	routerReload: vi.fn(),
}));

vi.mock("@inertiajs/react", () =>
	createInertiaReactMock({
		usePage: () => ({
			props: {
				selected_year: 2026,
				available_years: [2025, 2026],
				summary: {
					year: 2026,
					total_purchase_amount: 50000,
					total_payout_amount: 45000,
					total_net_amount: -5000,
					total_return_rate: 90.0,
				},
				daily_balances: [
					{
						date: "2026-04-05",
						purchase_amount: 3000,
						payout_amount: 5000,
						net_amount: 2000,
						return_rate: 166.7,
					},
				],
				next_cursor: "cursor-string",
			},
		}),
		router: { get: routerGet, reload: routerReload },
	}),
);

vi.mock("@/routes", () => ({
	dashboard: Object.assign(() => "/dashboard", {
		url: () => "/dashboard",
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
			<button type="button" onClick={() => onValueChange("2025")}>
				年選択
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

describe("Dashboard ページ", () => {
	it("ハッピーパス: Inertia propsのデータが BalanceDashboard に表示される", () => {
		// Act
		render(<Dashboard />);

		// Assert
		expect(document.title).toBe("収支ダッシュボード");
		expect(screen.getByText("収支ダッシュボード")).toBeInTheDocument();
		expect(screen.getByText("2026/04/05")).toBeInTheDocument();
	});

	it("年セレクタを変更すると router.get が選択された年のクエリパラメータで呼ばれる", async () => {
		// Arrange
		routerGet.mockClear();
		const { default: userEvent } = await import("@testing-library/user-event");
		const user = userEvent.setup();

		// Act
		render(<Dashboard />);
		await user.click(screen.getByRole("button", { name: "年選択" }));

		// Assert
		expect(routerGet).toHaveBeenCalledTimes(1);
		const callArgs = routerGet.mock.calls[0];
		const dataArg = callArgs[1] as { year: number };
		expect(dataArg.year).toBe(2025);
	});

	it("next_cursor がある場合、「もっと読み込む」ボタンをクリックすると router.reload が cursor パラメータと only: ['daily_balances', 'next_cursor'] で呼ばれる", async () => {
		// Arrange
		routerReload.mockClear();
		const { default: userEvent } = await import("@testing-library/user-event");
		const user = userEvent.setup();

		// Act
		render(<Dashboard />);
		await user.click(screen.getByRole("button", { name: "もっと読み込む" }));

		// Assert
		expect(routerReload).toHaveBeenCalledTimes(1);
		const callArgs = routerReload.mock.calls[0];
		const optionsArg = callArgs[0] as {
			data: { cursor: string };
			only: string[];
		};
		expect(optionsArg.data.cursor).toBe("cursor-string");
		expect(optionsArg.only).toEqual(["daily_balances", "next_cursor"]);
	});
});
