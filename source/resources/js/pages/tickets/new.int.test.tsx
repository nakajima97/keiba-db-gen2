import { render, screen } from "@testing-library/react";
import { describe, it, expect, vi } from "vitest";
import TicketsNew from "./new";

vi.mock("@inertiajs/react", () => ({
	Head: ({ title }: { title: string }) => <title>{title}</title>,
	usePage: () => ({
		props: {
			lastVenue: "東京",
			lastRaceDate: "2026-04-05",
			lastRaceNumber: 1,
		},
	}),
	router: {
		post: vi.fn(),
		visit: vi.fn(),
		reload: vi.fn(),
	},
}));

describe("TicketsNew ページ", () => {
	it("ハッピーパス: ページが正常にレンダリングされ、TicketPurchaseForm が表示される", () => {
		// Act
		render(<TicketsNew />);

		// Assert
		expect(screen.getByText("レース情報")).toBeInTheDocument();
		expect(screen.getByText("券種")).toBeInTheDocument();
		expect(screen.getByText("買い方")).toBeInTheDocument();
		expect(screen.getByText("馬番")).toBeInTheDocument();
		expect(screen.getByText("金額")).toBeInTheDocument();
		expect(
			screen.getByRole("button", { name: "登録する" }),
		).toBeInTheDocument();
	});
});
