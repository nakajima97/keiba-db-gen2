import { render, screen } from "@testing-library/react";
import { describe, it, expect } from "vitest";
import TicketPurchaseForm, {
	TICKET_TYPES,
	BUY_TYPE_MAP,
	getHorseInputConfigKey,
} from "./index";
import type { TicketPurchaseFormProps } from "./index";

const noop = () => {};

const baseProps: TicketPurchaseFormProps = {
	selectedVenue: "東京",
	selectedRaceDate: "2026-04-05",
	selectedRaceNumber: 1,
	selectedTicketTypeId: "umaren",
	selectedBuyTypeId: "nagashi",
	selectedAxisCount: 1,
	selectedNagashiDirection: 1,
	selectedHorses: { axis: [3], others: [1, 5, 7] },
	amount: 100,
	onVenueChange: noop,
	onRaceDateChange: noop,
	onRaceNumberChange: noop,
	onTicketTypeChange: noop,
	onBuyTypeChange: noop,
	onAxisCountChange: noop,
	onNagashiDirectionChange: noop,
	onHorsesChange: noop,
	onAmountChange: noop,
	processing: false,
};

describe("TicketPurchaseForm", () => {
	describe("レンダリング", () => {
		it("レース情報セクション（開催場所・開催日・レース番号）が表示される", () => {
			// Act
			render(<TicketPurchaseForm {...baseProps} />);

			// Assert
			expect(screen.getByText("レース情報")).toBeInTheDocument();
			expect(screen.getByLabelText("開催場所")).toBeInTheDocument();
			expect(screen.getByLabelText("開催日")).toBeInTheDocument();
			expect(screen.getByLabelText("レース番号を直接入力")).toBeInTheDocument();
		});

		it("券種セクション（8種類のボタン）が表示される", () => {
			// Act
			render(<TicketPurchaseForm {...baseProps} />);

			// Assert
			expect(screen.getByText("券種")).toBeInTheDocument();
			expect(TICKET_TYPES).toHaveLength(8);
			for (const { label } of TICKET_TYPES) {
				expect(screen.getByRole("button", { name: label })).toBeInTheDocument();
			}
		});

		it("買い方セクション（券種に対応したボタン群）が表示される", () => {
			// Act
			render(<TicketPurchaseForm {...baseProps} />);

			// Assert
			expect(screen.getByText("買い方")).toBeInTheDocument();
			const buyTypes = BUY_TYPE_MAP[baseProps.selectedTicketTypeId];
			for (const { label } of buyTypes) {
				expect(screen.getByRole("button", { name: label })).toBeInTheDocument();
			}
		});

		it("馬番セクションが表示される", () => {
			// Act
			render(<TicketPurchaseForm {...baseProps} />);

			// Assert
			expect(screen.getByText("馬番")).toBeInTheDocument();
		});

		it("金額セクション（入力欄・±ボタン・プリセット）が表示される", () => {
			// Act
			render(<TicketPurchaseForm {...baseProps} />);

			// Assert
			expect(screen.getByText("金額")).toBeInTheDocument();
			expect(screen.getByLabelText("購入金額（円）")).toBeInTheDocument();
			expect(screen.getByLabelText("100円減らす")).toBeInTheDocument();
			expect(screen.getByLabelText("100円増やす")).toBeInTheDocument();
			expect(screen.getByText("100円")).toBeInTheDocument();
			expect(screen.getByText("500円")).toBeInTheDocument();
			expect(screen.getByText("1,000円")).toBeInTheDocument();
		});

		it("登録ボタン・キャンセルボタンが表示される", () => {
			// Act
			render(<TicketPurchaseForm {...baseProps} />);

			// Assert
			expect(
				screen.getByRole("button", { name: "登録する" }),
			).toBeInTheDocument();
			expect(
				screen.getByRole("button", { name: "キャンセル" }),
			).toBeInTheDocument();
		});
	});

	describe("選択状態の表示", () => {
		it("selectedTicketTypeId に対応する券種ボタンだけ aria-pressed=true になる", () => {
			// Act
			render(
				<TicketPurchaseForm {...baseProps} selectedTicketTypeId="sanrenpuku" />,
			);

			// Assert
			for (const { id, label } of TICKET_TYPES) {
				const button = screen.getByRole("button", { name: label });
				if (id === "sanrenpuku") {
					expect(button).toHaveAttribute("aria-pressed", "true");
				} else {
					expect(button).toHaveAttribute("aria-pressed", "false");
				}
			}
		});

		it("selectedBuyTypeId に対応する買い方ボタンだけ aria-pressed=true になる", () => {
			// Act
			render(
				<TicketPurchaseForm
					{...baseProps}
					selectedTicketTypeId="umatan"
					selectedBuyTypeId="box"
				/>,
			);

			// Assert
			const buyTypes = BUY_TYPE_MAP.umatan;
			for (const { id, label } of buyTypes) {
				const button = screen.getByRole("button", { name: label });
				if (id === "box") {
					expect(button).toHaveAttribute("aria-pressed", "true");
				} else {
					expect(button).toHaveAttribute("aria-pressed", "false");
				}
			}
		});

		it("selectedRaceNumber に対応するレース番号ボタンだけ aria-pressed=true になる", () => {
			// Act
			render(<TicketPurchaseForm {...baseProps} selectedRaceNumber={5} />);

			// Assert
			const raceButton5 = screen.getByRole("button", { name: "5R" });
			expect(raceButton5).toHaveAttribute("aria-pressed", "true");
			const raceButton1 = screen.getByRole("button", { name: "1R" });
			expect(raceButton1).toHaveAttribute("aria-pressed", "false");
		});
	});

	describe("馬番グリッド", () => {
		it("枠連（wakuren）のグリッドは 1〜8 の 8 個表示される", () => {
			// Act
			render(
				<TicketPurchaseForm
					{...baseProps}
					selectedTicketTypeId="wakuren"
					selectedBuyTypeId="nagashi"
					selectedAxisCount={1}
					selectedHorses={{ axis: [], others: [] }}
				/>,
			);

			// Assert
			for (let i = 1; i <= 8; i++) {
				expect(
					screen.getAllByRole("button", { name: `${i}番` }).length,
				).toBeGreaterThanOrEqual(1);
			}
			expect(
				screen.queryByRole("button", { name: "9番" }),
			).not.toBeInTheDocument();
		});

		it("枠連以外のグリッドは 1〜18 の 18 個表示される", () => {
			// Act
			render(
				<TicketPurchaseForm
					{...baseProps}
					selectedTicketTypeId="umaren"
					selectedBuyTypeId="nagashi"
					selectedAxisCount={1}
					selectedHorses={{ axis: [], others: [] }}
				/>,
			);

			// Assert
			for (let i = 1; i <= 18; i++) {
				expect(
					screen.getAllByRole("button", { name: `${i}番` }).length,
				).toBeGreaterThanOrEqual(1);
			}
			expect(
				screen.queryByRole("button", { name: "19番" }),
			).not.toBeInTheDocument();
		});

		it("selectedHorses に含まれる馬番ボタンが aria-pressed=true になる", () => {
			// Act
			render(
				<TicketPurchaseForm
					{...baseProps}
					selectedTicketTypeId="tansho"
					selectedBuyTypeId="single"
					selectedHorses={{ horses: [3, 7] }}
				/>,
			);

			// Assert
			expect(screen.getByRole("button", { name: "3番" })).toHaveAttribute(
				"aria-pressed",
				"true",
			);
			expect(screen.getByRole("button", { name: "7番" })).toHaveAttribute(
				"aria-pressed",
				"true",
			);
			expect(screen.getByRole("button", { name: "1番" })).toHaveAttribute(
				"aria-pressed",
				"false",
			);
		});
	});

	describe("条件付き UI 要素", () => {
		it("三連複（sanrenpuku）+ 流し（nagashi）のとき「軸の頭数」セレクタが表示される", () => {
			// Act
			render(
				<TicketPurchaseForm
					{...baseProps}
					selectedTicketTypeId="sanrenpuku"
					selectedBuyTypeId="nagashi"
					selectedAxisCount={1}
					selectedHorses={{ axis: [], others: [] }}
				/>,
			);

			// Assert
			expect(screen.getByText("軸の頭数")).toBeInTheDocument();
			expect(screen.getByRole("button", { name: "1頭軸" })).toBeInTheDocument();
			expect(screen.getByRole("button", { name: "2頭軸" })).toBeInTheDocument();
		});

		it("三連単（sanrentan）+ 流し（nagashi）のとき「流し方向」セレクタが表示される", () => {
			// Act
			render(
				<TicketPurchaseForm
					{...baseProps}
					selectedTicketTypeId="sanrentan"
					selectedBuyTypeId="nagashi"
					selectedNagashiDirection={1}
					selectedHorses={{ col1: [], col2: [], col3: [] }}
				/>,
			);

			// Assert
			expect(screen.getByText("流し方向")).toBeInTheDocument();
			expect(
				screen.getByRole("button", { name: "1着流し" }),
			).toBeInTheDocument();
			expect(
				screen.getByRole("button", { name: "2着流し" }),
			).toBeInTheDocument();
			expect(
				screen.getByRole("button", { name: "3着流し" }),
			).toBeInTheDocument();
		});

		it("上記以外の組み合わせでは「軸の頭数」「流し方向」セレクタが表示されない", () => {
			// Act
			render(
				<TicketPurchaseForm
					{...baseProps}
					selectedTicketTypeId="umaren"
					selectedBuyTypeId="nagashi"
					selectedHorses={{ axis: [], others: [] }}
				/>,
			);

			// Assert
			expect(screen.queryByText("軸の頭数")).not.toBeInTheDocument();
			expect(screen.queryByText("流し方向")).not.toBeInTheDocument();
		});
	});
});

describe("getHorseInputConfigKey", () => {
	it("buyTypeId が single のとき single を返す", () => {
		// Act
		const result = getHorseInputConfigKey("tansho", "single", 1, 1);

		// Assert
		expect(result).toBe("single");
	});

	it("buyTypeId が box のとき box を返す", () => {
		// Act
		const result = getHorseInputConfigKey("umaren", "box", 1, 1);

		// Assert
		expect(result).toBe("box");
	});

	it("nagashi + umaren（axisCount=1）のとき nagashi_axis1 を返す", () => {
		// Act
		const result = getHorseInputConfigKey("umaren", "nagashi", 1, 1);

		// Assert
		expect(result).toBe("nagashi_axis1");
	});

	it("nagashi + sanrenpuku + axisCount=2 のとき nagashi_axis2 を返す", () => {
		// Act
		const result = getHorseInputConfigKey("sanrenpuku", "nagashi", 2, 1);

		// Assert
		expect(result).toBe("nagashi_axis2");
	});

	it("nagashi + sanrentan のとき formation を返す", () => {
		// Act
		const result = getHorseInputConfigKey("sanrentan", "nagashi", 1, 1);

		// Assert
		expect(result).toBe("formation");
	});

	it("buyTypeId が formation のとき formation を返す", () => {
		// Act
		const result = getHorseInputConfigKey("sanrentan", "formation", 1, 1);

		// Assert
		expect(result).toBe("formation");
	});
});
