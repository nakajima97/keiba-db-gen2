import { render, screen } from "@testing-library/react";
import { describe, it, expect } from "vitest";
import RaceDetail from "./index";
import type { RaceDetailItem } from "./types";

const baseRace: RaceDetailItem = {
	uid: "abc123",
	race_date: "2026-04-05",
	venue_name: "東京",
	race_number: 3,
	entries: [
		{
			frame_number: 2,
			horse_number: 1,
			horse_name: "テストホース",
			jockey_name: "テスト騎手",
			weight: 480,
		},
	],
};

describe("RaceDetail", () => {
	describe("基本情報", () => {
		it("開催日が表示される", () => {
			// Act
			render(<RaceDetail race={baseRace} />);

			// Assert
			expect(screen.getByText("2026/04/05")).toBeInTheDocument();
		});

		it("競馬場が表示される", () => {
			// Act
			render(<RaceDetail race={baseRace} />);

			// Assert
			expect(screen.getByText("東京")).toBeInTheDocument();
		});

		it("レース番号が「3R」形式で表示される", () => {
			// Act
			render(<RaceDetail race={baseRace} />);

			// Assert
			expect(screen.getByText("3R")).toBeInTheDocument();
		});
	});

	describe("出馬表", () => {
		it("枠番・馬番・馬名・騎手名・馬体重が表示される", () => {
			// Act
			render(<RaceDetail race={baseRace} />);

			// Assert
			expect(screen.getByText("1")).toBeInTheDocument();
			expect(screen.getByText("テストホース")).toBeInTheDocument();
			expect(screen.getByText("テスト騎手")).toBeInTheDocument();
			expect(screen.getByText("480kg")).toBeInTheDocument();
		});

		it("馬体重が null の場合「-」が表示される", () => {
			// Arrange
			const raceWithNullWeight: RaceDetailItem = {
				...baseRace,
				entries: [{ ...baseRace.entries[0], weight: null }],
			};

			// Act
			render(<RaceDetail race={raceWithNullWeight} />);

			// Assert
			expect(screen.getByText("-")).toBeInTheDocument();
		});
	});
});
