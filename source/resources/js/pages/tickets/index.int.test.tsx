import { router } from "@inertiajs/react";
import { render, screen } from "@testing-library/react";
import userEvent from "@testing-library/user-event";
import { beforeEach, describe, expect, it, vi } from "vitest";
import TicketsIndex from "./index";

vi.mock("@inertiajs/react", () => ({
	Head: ({ title }: { title: string }) => <title>{title}</title>,
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
	router: {
		reload: vi.fn(),
		delete: vi.fn(),
	},
	Link: ({ href, children }: { href: string; children: unknown }) => (
		<a href={href}>{children as never}</a>
	),
}));

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
