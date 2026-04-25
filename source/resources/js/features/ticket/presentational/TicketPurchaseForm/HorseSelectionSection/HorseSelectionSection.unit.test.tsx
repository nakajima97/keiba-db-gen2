import { render, screen } from "@testing-library/react";
import { describe, expect, it } from "vitest";
import { HorseSelectionSection } from ".";
import type { HorseSelectionSectionProps } from "./types";

const noop = () => {};

const baseProps: HorseSelectionSectionProps = {
	selectedTicketTypeId: "umaren",
	selectedBuyTypeId: "nagashi",
	selectedAxisCount: 1,
	selectedNagashiDirection: 1,
	selectedHorses: { axis: [3], others: [1, 5, 7] },
	onAxisCountChange: noop,
	onNagashiDirectionChange: noop,
	onHorsesChange: noop,
};

describe("HorseSelectionSection", () => {
	describe("レンダリング", () => {
		it("「馬番」セクションタイトルが表示される", () => {
			// Act
			render(<HorseSelectionSection {...baseProps} />);

			// Assert
			expect(screen.getByText("馬番")).toBeInTheDocument();
		});
	});

	describe("馬番グリッド", () => {
		it("枠連（wakuren）のグリッドは 1〜8 の 8 個表示される", () => {
			// Act
			render(
				<HorseSelectionSection
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
				<HorseSelectionSection
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
				<HorseSelectionSection
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
				<HorseSelectionSection
					{...baseProps}
					selectedTicketTypeId="sanrenpuku"
					selectedBuyTypeId="nagashi"
					selectedAxisCount={1}
					selectedHorses={{ axis: [], others: [] }}
				/>,
			);

			// Assert
			expect(screen.getByText("軸の頭数")).toBeInTheDocument();
			expect(
				screen.getByRole("button", { name: "1頭軸" }),
			).toBeInTheDocument();
			expect(
				screen.getByRole("button", { name: "2頭軸" }),
			).toBeInTheDocument();
		});

		it("三連単（sanrentan）+ 流し（nagashi）のとき「流し方向」セレクタが表示される", () => {
			// Act
			render(
				<HorseSelectionSection
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

		it("三連複（sanrenpuku）+ フォーメーション（formation）のとき「1列目」「2列目」「3列目」ラベルが表示される", () => {
			// Act
			render(
				<HorseSelectionSection
					{...baseProps}
					selectedTicketTypeId="sanrenpuku"
					selectedBuyTypeId="formation"
					selectedHorses={{ col1: [], col2: [], col3: [] }}
				/>,
			);

			// Assert
			expect(screen.getByText("1列目")).toBeInTheDocument();
			expect(screen.getByText("2列目")).toBeInTheDocument();
			expect(screen.getByText("3列目")).toBeInTheDocument();
		});

		it("三連複（sanrenpuku）+ フォーメーション（formation）のとき「軸の頭数」セレクタが表示されない", () => {
			// Act
			render(
				<HorseSelectionSection
					{...baseProps}
					selectedTicketTypeId="sanrenpuku"
					selectedBuyTypeId="formation"
					selectedHorses={{ col1: [], col2: [], col3: [] }}
				/>,
			);

			// Assert
			expect(screen.queryByText("軸の頭数")).not.toBeInTheDocument();
		});

		it("三連単（sanrentan）+ 流し（nagashi）以外では「流し方向」セレクタが表示されない", () => {
			// Act
			render(
				<HorseSelectionSection
					{...baseProps}
					selectedTicketTypeId="umaren"
					selectedBuyTypeId="nagashi"
					selectedHorses={{ axis: [], others: [] }}
				/>,
			);

			// Assert
			expect(screen.queryByText("流し方向")).not.toBeInTheDocument();
		});
	});
});
