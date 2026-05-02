import { render, screen } from "@testing-library/react";
import userEvent from "@testing-library/user-event";
import { describe, it, expect, vi } from "vitest";
import TicketPurchaseList from "./index";
import type { TicketPurchaseListItem, TicketPurchaseListProps } from "./types";

vi.mock("@inertiajs/react", () => ({
	Link: ({ href, children }: { href: string; children: unknown }) => (
		<a href={href}>{children as never}</a>
	),
}));

vi.mock("@/routes/tickets", () => ({
	newMethod: {
		url: () => "/tickets/new",
	},
}));

const noop = () => {};

const samplePurchase: TicketPurchaseListItem = {
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
	amount: 100,
	payout_amount: 500,
};

const baseProps: TicketPurchaseListProps = {
	purchases: [samplePurchase],
	hasMore: false,
	isLoading: false,
	onLoadMore: noop,
};

describe("TicketPurchaseList", () => {
	describe("空の状態", () => {
		it("「まだ購入記録がありません」が表示される", () => {
			// Act
			render(<TicketPurchaseList {...baseProps} purchases={[]} />);

			// Assert
			expect(screen.getByText("まだ購入記録がありません")).toBeInTheDocument();
		});

		it("「馬券を登録する」リンクが表示される", () => {
			// Act
			render(<TicketPurchaseList {...baseProps} purchases={[]} />);

			// Assert
			expect(screen.getByText("馬券を登録する")).toBeInTheDocument();
		});

		it("テーブルが表示されない", () => {
			// Act
			render(<TicketPurchaseList {...baseProps} purchases={[]} />);

			// Assert
			expect(screen.queryByRole("table")).not.toBeInTheDocument();
		});
	});

	describe("データあり", () => {
		it("テーブルヘッダーが表示される（日付・レース場・レース番号・券種・買い方・点数・購入金額）", () => {
			// Act
			render(<TicketPurchaseList {...baseProps} />);

			// Assert
			expect(screen.getByText("日付")).toBeInTheDocument();
			expect(screen.getByText("レース場")).toBeInTheDocument();
			expect(screen.getByText("レース番号")).toBeInTheDocument();
			expect(screen.getByText("券種")).toBeInTheDocument();
			expect(screen.getByText("買い方")).toBeInTheDocument();
			expect(screen.getByText("点数")).toBeInTheDocument();
			expect(screen.getByText("購入金額")).toBeInTheDocument();
		});

		it("race_date が YYYY/MM/DD 形式で表示される（2026-04-05 → 2026/04/05）", () => {
			// Act
			render(<TicketPurchaseList {...baseProps} />);

			// Assert
			expect(screen.getByText("2026/04/05")).toBeInTheDocument();
		});

		it("race_date が null のとき「-」が表示される", () => {
			// Arrange
			const purchaseWithNullDate: TicketPurchaseListItem = {
				...samplePurchase,
				race_date: null,
				race_uid: "sample-uid",
			};

			// Act
			render(
				<TicketPurchaseList
					{...baseProps}
					purchases={[purchaseWithNullDate]}
				/>,
			);

			// Assert
			expect(screen.getByText("-")).toBeInTheDocument();
		});

		it("amount が null のとき「-」が表示される", () => {
			// Arrange
			const purchaseWithNullAmount: TicketPurchaseListItem = {
				...samplePurchase,
				amount: null,
				race_uid: "sample-uid",
			};

			// Act
			render(
				<TicketPurchaseList
					{...baseProps}
					purchases={[purchaseWithNullAmount]}
				/>,
			);

			// Assert
			expect(screen.getByText("-")).toBeInTheDocument();
		});

		it("ヘッダー右上に「馬券を登録する」リンクが表示される", () => {
			// Act
			render(<TicketPurchaseList {...baseProps} />);

			// Assert
			expect(screen.getByText("馬券を登録する")).toBeInTheDocument();
		});

		it("「馬券を登録する」リンクの遷移先が /tickets/new である", () => {
			// Act
			render(<TicketPurchaseList {...baseProps} />);

			// Assert
			const link = screen.getByText("馬券を登録する");
			expect(link).toHaveAttribute("href", "/tickets/new");
		});

		it("テーブルヘッダーに「払い戻し金額」が表示される", () => {
			// Act
			render(<TicketPurchaseList {...baseProps} />);

			// Assert
			expect(screen.getByText("払い戻し金額")).toBeInTheDocument();
		});

		it("payout_amount が数値の場合、¥フォーマットで表示される（例: ¥5,000）", () => {
			// Arrange
			const purchaseWithPayout: TicketPurchaseListItem = {
				...samplePurchase,
				payout_amount: 5000,
			};

			// Act
			render(
				<TicketPurchaseList {...baseProps} purchases={[purchaseWithPayout]} />,
			);

			// Assert
			expect(screen.getByText("¥5,000")).toBeInTheDocument();
		});

		it("payout_amount が null の場合、払い戻し金額列に「-」が表示される", () => {
			// Arrange
			const purchaseWithNullPayout: TicketPurchaseListItem = {
				...samplePurchase,
				payout_amount: null,
			};

			// Act
			render(
				<TicketPurchaseList
					{...baseProps}
					purchases={[purchaseWithNullPayout]}
				/>,
			);

			// Assert
			const dashes = screen.getAllByText("-");
			expect(dashes.length).toBeGreaterThanOrEqual(1);
		});

		it("num_combinations の値が数字で表示される（例: 3）", () => {
			// Arrange
			const purchaseWithCombinations: TicketPurchaseListItem = {
				...samplePurchase,
				num_combinations: 3,
			};

			// Act
			render(
				<TicketPurchaseList
					{...baseProps}
					purchases={[purchaseWithCombinations]}
				/>,
			);

			// Assert
			expect(screen.getByText("3")).toBeInTheDocument();
		});
	});

	describe("ページネーション", () => {
		it("hasMore=false のとき「もっと読み込む」ボタンが表示されない", () => {
			// Act
			render(<TicketPurchaseList {...baseProps} hasMore={false} />);

			// Assert
			expect(
				screen.queryByRole("button", { name: "もっと読み込む" }),
			).not.toBeInTheDocument();
		});

		it("hasMore=true のとき「もっと読み込む」ボタンが表示される", () => {
			// Act
			render(<TicketPurchaseList {...baseProps} hasMore={true} />);

			// Assert
			expect(
				screen.getByRole("button", { name: "もっと読み込む" }),
			).toBeInTheDocument();
		});

		it("isLoading=true のとき「読み込み中...」が表示される", () => {
			// Act
			render(
				<TicketPurchaseList {...baseProps} hasMore={true} isLoading={true} />,
			);

			// Assert
			expect(screen.getByText("読み込み中...")).toBeInTheDocument();
		});

		it("isLoading=true のとき「もっと読み込む」ボタンが disabled になる", () => {
			// Act
			render(
				<TicketPurchaseList {...baseProps} hasMore={true} isLoading={true} />,
			);

			// Assert
			const button = screen.getByRole("button", { name: /読み込み中/ });
			expect(button).toBeDisabled();
		});

		it("「もっと読み込む」ボタンをクリックすると onLoadMore が呼ばれる", async () => {
			// Arrange
			const onLoadMore = vi.fn();
			const user = userEvent.setup();

			// Act
			render(
				<TicketPurchaseList
					{...baseProps}
					hasMore={true}
					onLoadMore={onLoadMore}
				/>,
			);
			await user.click(screen.getByRole("button", { name: "もっと読み込む" }));

			// Assert
			expect(onLoadMore).toHaveBeenCalledTimes(1);
		});
	});
});
