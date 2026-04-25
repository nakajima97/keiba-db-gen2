import { render, screen } from "@testing-library/react";
import { describe, expect, it } from "vitest";
import { RaceInfoSection } from ".";
import type { RaceInfoSectionProps } from "./types";

const noop = () => {};

const baseProps: RaceInfoSectionProps = {
	selectedVenue: "東京",
	selectedRaceDate: "2026-04-05",
	selectedRaceNumber: 1,
	onVenueChange: noop,
	onRaceDateChange: noop,
	onRaceNumberChange: noop,
};

describe("RaceInfoSection", () => {
	describe("レンダリング", () => {
		it("「レース情報」セクションタイトルが表示される", () => {
			// Act
			render(<RaceInfoSection {...baseProps} />);

			// Assert
			expect(screen.getByText("レース情報")).toBeInTheDocument();
		});

		it("「開催場所」ラベルとSelectが表示される", () => {
			// Act
			render(<RaceInfoSection {...baseProps} />);

			// Assert
			expect(screen.getByLabelText("開催場所")).toBeInTheDocument();
		});

		it("「開催日」ラベルとdate inputが表示される", () => {
			// Act
			render(<RaceInfoSection {...baseProps} />);

			// Assert
			expect(screen.getByLabelText("開催日")).toBeInTheDocument();
		});

		it("「レース番号」ラベルと 1〜12 のボタンが表示される", () => {
			// Act
			render(<RaceInfoSection {...baseProps} />);

			// Assert
			expect(
				screen.getByLabelText("レース番号を直接入力"),
			).toBeInTheDocument();
			for (let r = 1; r <= 12; r++) {
				expect(
					screen.getByRole("button", { name: `${r}R` }),
				).toBeInTheDocument();
			}
		});
	});

	describe("選択状態の表示", () => {
		it("selectedRaceNumber に対応するレース番号ボタンのみ aria-pressed=true になる", () => {
			// Act
			render(<RaceInfoSection {...baseProps} selectedRaceNumber={5} />);

			// Assert
			expect(screen.getByRole("button", { name: "5R" })).toHaveAttribute(
				"aria-pressed",
				"true",
			);
			expect(screen.getByRole("button", { name: "1R" })).toHaveAttribute(
				"aria-pressed",
				"false",
			);
		});
	});
});
