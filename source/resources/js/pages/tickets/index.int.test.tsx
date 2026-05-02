import { render, screen } from "@testing-library/react";
import { describe, it, expect, vi } from "vitest";
import TicketsIndex from "./index";

vi.mock("@inertiajs/react", () => ({
	Head: ({ title }: { title: string }) => <title>{title}</title>,
	usePage: () => ({
		props: {
			purchases: [
				{
					id: 1,
					race_date: "2026-04-05",
					venue_name: "東京",
					race_number: 1,
					ticket_type_label: "馬連",
					buy_type_name: "nagashi",
					selections: { axis: [1], others: [2, 4, 6] },
					unit_stake: 100,
					payout_amount: null,
				},
			],
			nextCursor: null,
		},
	}),
	router: {
		reload: vi.fn(),
	},
	Link: ({ href, children }: { href: string; children: unknown }) => (
		<a href={href}>{children as never}</a>
	),
}));

vi.mock("@/routes/tickets", () => ({
	newMethod: {
		url: () => "/tickets/new",
	},
}));

describe("TicketsIndex ページ", () => {
	it("ハッピーパス: Inertia propsのデータがTicketPurchaseListに表示される", () => {
		// Act
		render(<TicketsIndex />);

		// Assert
		expect(screen.getByText("購入馬券一覧")).toBeInTheDocument();
		expect(screen.getByText("2026/04/05")).toBeInTheDocument();
		expect(screen.getByText("東京")).toBeInTheDocument();
		expect(screen.getByText("馬連")).toBeInTheDocument();
	});
});
