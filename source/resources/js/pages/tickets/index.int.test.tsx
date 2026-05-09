import { render, screen } from "@testing-library/react";
import userEvent from "@testing-library/user-event";
import { beforeEach, describe, expect, it, vi } from "vitest";
import { createInertiaReactMock } from "@/tests/mocks";

const { routerReload, routerDelete } = vi.hoisted(() => ({
	routerReload: vi.fn(),
	routerDelete: vi.fn(),
}));

vi.mock("@inertiajs/react", () =>
	createInertiaReactMock({
		usePage: () => ({
			props: {
				purchases: [
					{
						id: 1,
						race_uid: null,
						has_race_result: false,
						race_date: "2026-04-05",
						venue_name: "東京",
						race_number: 1,
						ticket_type_label: "馬連",
						buy_type_name: "nagashi",
						selections: { axis: [1], others: [2, 4, 6] },
						num_combinations: 3,
						purchase_amount: 100,
						payout_amount: null,
					},
				],
				nextCursor: null,
			},
		}),
		router: { reload: routerReload, delete: routerDelete },
	}),
);

// vi.mock factory が createInertiaReactMock を参照するため、`@inertiajs/react` の import は vi.mock の後に置く（前に置くと __vi_import 未初期化エラー）
import { router } from "@inertiajs/react";
import TicketsIndex from "./index";

vi.mock("@/routes/tickets", () => ({
	newMethod: {
		url: () => "/tickets/new",
	},
	destroy: {
		url: (id: number) => `/tickets/${id}`,
	},
}));

describe("TicketsIndex ページ", () => {
	beforeEach(() => {
		vi.mocked(router.delete).mockClear();
		vi.mocked(router.reload).mockClear();
	});

	it("ハッピーパス: Inertia propsのデータがTicketPurchaseListに表示される", () => {
		// Act
		render(<TicketsIndex />);

		// Assert
		expect(screen.getByText("購入馬券一覧")).toBeInTheDocument();
		expect(screen.getByText("2026/04/05")).toBeInTheDocument();
		expect(screen.getByText("東京")).toBeInTheDocument();
		expect(screen.getByText("馬連")).toBeInTheDocument();
	});

	it("削除ボタンをクリックすると確認モーダルが開き、router.delete はまだ呼ばれない", async () => {
		// Arrange
		const user = userEvent.setup();

		// Act
		render(<TicketsIndex />);
		await user.click(screen.getByRole("button", { name: "削除" }));

		// Assert
		expect(
			screen.getByRole("button", { name: "削除する" }),
		).toBeInTheDocument();
		expect(vi.mocked(router.delete)).not.toHaveBeenCalled();
	});

	it("確認モーダルで「削除する」を押すと router.delete が呼ばれる", async () => {
		// Arrange
		const user = userEvent.setup();

		// Act
		render(<TicketsIndex />);
		await user.click(screen.getByRole("button", { name: "削除" }));
		await user.click(screen.getByRole("button", { name: "削除する" }));

		// Assert
		expect(vi.mocked(router.delete)).toHaveBeenCalled();
	});

	it("確認モーダルには削除対象馬券のサマリー（日付・レース場・レース番号・券種）が表示される", async () => {
		// Arrange
		const user = userEvent.setup();

		// Act
		render(<TicketsIndex />);
		await user.click(screen.getByRole("button", { name: "削除" }));

		// Assert
		const dialog = screen.getByRole("dialog");
		expect(dialog).toHaveTextContent("2026/04/05");
		expect(dialog).toHaveTextContent("東京");
		expect(dialog).toHaveTextContent("1R");
		expect(dialog).toHaveTextContent("馬連");
	});

	it("「キャンセル」を押すとモーダルが閉じ、router.delete は呼ばれない", async () => {
		// Arrange
		const user = userEvent.setup();

		// Act
		render(<TicketsIndex />);
		await user.click(screen.getByRole("button", { name: "削除" }));
		await user.click(screen.getByRole("button", { name: "キャンセル" }));

		// Assert
		expect(
			screen.queryByRole("button", { name: "削除する" }),
		).not.toBeInTheDocument();
		expect(vi.mocked(router.delete)).not.toHaveBeenCalled();
	});
});
