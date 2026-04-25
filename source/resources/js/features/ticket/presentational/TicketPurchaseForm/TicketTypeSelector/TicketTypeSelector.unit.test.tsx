import { render, screen } from "@testing-library/react";
import { describe, expect, it } from "vitest";
import { TicketTypeSelector } from ".";
import { BUY_TYPE_MAP, TICKET_TYPES } from "../constants";
import type { TicketTypeSelectorProps } from "./types";

const noop = () => {};

const baseProps: TicketTypeSelectorProps = {
	selectedTicketTypeId: "umaren",
	selectedBuyTypeId: "nagashi",
	onTicketTypeChange: noop,
	onBuyTypeChange: noop,
};

describe("TicketTypeSelector", () => {
	describe("レンダリング", () => {
		it("8種類の券種ボタンがすべて表示される", () => {
			// Act
			render(<TicketTypeSelector {...baseProps} />);

			// Assert
			expect(TICKET_TYPES).toHaveLength(8);
			for (const { label } of TICKET_TYPES) {
				expect(
					screen.getByRole("button", { name: label }),
				).toBeInTheDocument();
			}
		});

		it("selectedTicketTypeId に対応する買い方ボタンが表示される", () => {
			// Act
			render(
				<TicketTypeSelector
					{...baseProps}
					selectedTicketTypeId="umatan"
					selectedBuyTypeId="nagashi"
				/>,
			);

			// Assert
			const buyTypes = BUY_TYPE_MAP.umatan;
			for (const { label } of buyTypes) {
				expect(
					screen.getByRole("button", { name: label }),
				).toBeInTheDocument();
			}
		});
	});

	describe("選択状態の表示", () => {
		it("selectedTicketTypeId に対応する券種ボタンのみ aria-pressed=true になる", () => {
			// Act
			render(
				<TicketTypeSelector
					{...baseProps}
					selectedTicketTypeId="sanrenpuku"
				/>,
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

		it("selectedBuyTypeId に対応する買い方ボタンのみ aria-pressed=true になる", () => {
			// Act
			render(
				<TicketTypeSelector
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
	});
});
