import { render, screen } from "@testing-library/react";
import { describe, it, expect } from "vitest";
import RaceMyTicketSection from "./index";
import type { RaceMyTicket } from "./types";

const baseTicket: RaceMyTicket = {
	id: 1,
	ticket_type_label: "馬連",
	buy_type_name: "box",
	buy_type_label: "ボックス",
	selections: { horses: [1, 3, 7] },
	purchase_amount: 1000,
	payout_amount: 2500,
};

describe("RaceMyTicketSection", () => {
	it("tickets が空配列のときセクション見出しが表示されない", () => {
		// Act
		render(<RaceMyTicketSection tickets={[]} />);

		// Assert
		expect(
			screen.queryByRole("heading", { name: "自分の購入馬券" }),
		).not.toBeInTheDocument();
	});

	it("tickets が1件以上あるとき「自分の購入馬券」見出しが表示される", () => {
		// Act
		render(<RaceMyTicketSection tickets={[baseTicket]} />);

		// Assert
		expect(
			screen.getByRole("heading", { name: "自分の購入馬券" }),
		).toBeInTheDocument();
	});

	it("horses 形式の選択馬番が `1/3/7` 形式でセルに表示される", () => {
		// Arrange
		const ticket: RaceMyTicket = {
			...baseTicket,
			selections: { horses: [1, 3, 7] },
		};

		// Act
		render(<RaceMyTicketSection tickets={[ticket]} />);

		// Assert
		expect(screen.getByText("1/3/7")).toBeInTheDocument();
	});

	it("axis/others 形式の選択馬番が `軸: 1 / 相手: 3/7` 形式でセルに表示される", () => {
		// Arrange
		const ticket: RaceMyTicket = {
			...baseTicket,
			selections: { axis: [1], others: [3, 7] },
		};

		// Act
		render(<RaceMyTicketSection tickets={[ticket]} />);

		// Assert
		expect(screen.getByText("軸: 1 / 相手: 3/7")).toBeInTheDocument();
	});

	it("axis のみ（others なし）の選択馬番が `軸: 1` 形式でセルに表示される", () => {
		// Arrange
		const ticket: RaceMyTicket = {
			...baseTicket,
			selections: { axis: [1] },
		};

		// Act
		render(<RaceMyTicketSection tickets={[ticket]} />);

		// Assert
		expect(screen.getByText("軸: 1")).toBeInTheDocument();
	});

	it("columns 形式の選択馬番が `1列目: 1/2 / 2列目: 3/4` 形式でセルに表示される", () => {
		// Arrange
		const ticket: RaceMyTicket = {
			...baseTicket,
			selections: { columns: [[1, 2], [3, 4]] },
		};

		// Act
		render(<RaceMyTicketSection tickets={[ticket]} />);

		// Assert
		expect(screen.getByText("1列目: 1/2 / 2列目: 3/4")).toBeInTheDocument();
	});

	it("purchase_amount が null のとき購入金額セルに `-` が表示される", () => {
		// Arrange
		const ticket: RaceMyTicket = {
			...baseTicket,
			purchase_amount: null,
		};

		// Act
		render(<RaceMyTicketSection tickets={[ticket]} />);

		// Assert
		expect(screen.getByText("-")).toBeInTheDocument();
	});

	it("purchase_amount が数値のとき購入金額セルに `¥1,000` 形式で表示される", () => {
		// Arrange
		const ticket: RaceMyTicket = {
			...baseTicket,
			purchase_amount: 1000,
		};

		// Act
		render(<RaceMyTicketSection tickets={[ticket]} />);

		// Assert
		expect(screen.getByText(`¥${(1000).toLocaleString()}`)).toBeInTheDocument();
	});

	it("payout_amount が null のとき当落セルに `—` が表示される", () => {
		// Arrange
		const ticket: RaceMyTicket = {
			...baseTicket,
			payout_amount: null,
		};

		// Act
		render(<RaceMyTicketSection tickets={[ticket]} />);

		// Assert
		expect(screen.getByText("—")).toBeInTheDocument();
	});

	it("payout_amount > 0 のとき「的中」バッジが表示される", () => {
		// Arrange
		const ticket: RaceMyTicket = {
			...baseTicket,
			payout_amount: 2500,
		};

		// Act
		render(<RaceMyTicketSection tickets={[ticket]} />);

		// Assert
		expect(screen.getByText("的中")).toBeInTheDocument();
	});

	it("payout_amount === 0 のとき「ハズレ」バッジが表示される", () => {
		// Arrange
		const ticket: RaceMyTicket = {
			...baseTicket,
			payout_amount: 0,
		};

		// Act
		render(<RaceMyTicketSection tickets={[ticket]} />);

		// Assert
		expect(screen.getByText("ハズレ")).toBeInTheDocument();
	});
});
