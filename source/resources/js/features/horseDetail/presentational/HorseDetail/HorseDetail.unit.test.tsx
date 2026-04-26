import { render, screen } from "@testing-library/react";
import { describe, expect, it } from "vitest";
import HorseDetail from "./index";
import type { HorseDetailProps, RaceHistoryItem } from "./types";

const baseRaceHistory: RaceHistoryItem = {
	race_uid: "race-uid-1",
	race_date: "2026-04-05",
	venue_name: "東京",
	race_number: 5,
	race_name: "東京優駿",
	finishing_order: 3,
	jockey_name: "武豊",
	popularity: 2,
};

const baseProps: HorseDetailProps = {
	horse: {
		id: 1,
		name: "テストホース",
		birth_year: 2020,
		race_histories: [],
	},
};

describe("HorseDetail", () => {
	describe("レンダリング", () => {
		it("馬名が表示される", () => {
			// Act
			render(<HorseDetail {...baseProps} />);

			// Assert
			expect(screen.getByText("テストホース")).toBeInTheDocument();
		});

		it("生年が「{birth_year}年」形式で表示される", () => {
			// Act
			render(<HorseDetail {...baseProps} />);

			// Assert
			expect(screen.getByText("2020年")).toBeInTheDocument();
		});

		it("「レース履歴」見出しが表示される", () => {
			// Act
			render(<HorseDetail {...baseProps} />);

			// Assert
			expect(screen.getByText("レース履歴")).toBeInTheDocument();
		});
	});

	describe("レース履歴0件", () => {
		it("race_histories が空配列のとき「レース履歴がありません」が表示される", () => {
			// Act
			render(<HorseDetail {...baseProps} />);

			// Assert
			expect(screen.getByText("レース履歴がありません")).toBeInTheDocument();
		});

		it("race_histories が空配列のときレース履歴テーブル（開催日ヘッダー）が表示されない", () => {
			// Act
			render(<HorseDetail {...baseProps} />);

			// Assert
			expect(screen.queryByText("開催日")).not.toBeInTheDocument();
		});
	});

	describe("レース履歴あり", () => {
		const propsWithHistory: HorseDetailProps = {
			horse: {
				...baseProps.horse,
				race_histories: [baseRaceHistory],
			},
		};

		it("レース履歴テーブルのヘッダーが表示される", () => {
			// Act
			render(<HorseDetail {...propsWithHistory} />);

			// Assert
			expect(screen.getByText("開催日")).toBeInTheDocument();
			expect(screen.getByText("競馬場")).toBeInTheDocument();
			expect(screen.getByText("レース番号")).toBeInTheDocument();
			expect(screen.getByText("レース名")).toBeInTheDocument();
			expect(screen.getByText("着順")).toBeInTheDocument();
			expect(screen.getByText("騎手")).toBeInTheDocument();
			expect(screen.getByText("人気")).toBeInTheDocument();
		});

		it("開催日が formatDateDisplay で変換されて表示される", () => {
			// Act
			render(<HorseDetail {...propsWithHistory} />);

			// Assert
			expect(screen.getByText("2026/04/05")).toBeInTheDocument();
		});

		it("競馬場名が表示される", () => {
			// Act
			render(<HorseDetail {...propsWithHistory} />);

			// Assert
			expect(screen.getByText("東京")).toBeInTheDocument();
		});

		it("騎手名が表示される", () => {
			// Act
			render(<HorseDetail {...propsWithHistory} />);

			// Assert
			expect(screen.getByText("武豊")).toBeInTheDocument();
		});

		it("レース番号が「{n}R」形式で表示される", () => {
			// Act
			render(<HorseDetail {...propsWithHistory} />);

			// Assert
			expect(screen.getByText("5R")).toBeInTheDocument();
		});

		it("着順が「{n}着」形式で表示される", () => {
			// Act
			render(<HorseDetail {...propsWithHistory} />);

			// Assert
			expect(screen.getByText("3着")).toBeInTheDocument();
		});

		it("人気が「{n}番人気」形式で表示される", () => {
			// Act
			render(<HorseDetail {...propsWithHistory} />);

			// Assert
			expect(screen.getByText("2番人気")).toBeInTheDocument();
		});

		it("「レース履歴がありません」は表示されない", () => {
			// Act
			render(<HorseDetail {...propsWithHistory} />);

			// Assert
			expect(
				screen.queryByText("レース履歴がありません"),
			).not.toBeInTheDocument();
		});
	});

	describe("race_name の扱い", () => {
		it("race_name が null のとき「—」が表示される", () => {
			// Arrange
			const props: HorseDetailProps = {
				horse: {
					...baseProps.horse,
					race_histories: [{ ...baseRaceHistory, race_name: null }],
				},
			};

			// Act
			render(<HorseDetail {...props} />);

			// Assert
			expect(screen.getByText("—")).toBeInTheDocument();
		});

		it("race_name が文字列のときその値が表示される", () => {
			// Arrange
			const props: HorseDetailProps = {
				horse: {
					...baseProps.horse,
					race_histories: [{ ...baseRaceHistory, race_name: "東京優駿" }],
				},
			};

			// Act
			render(<HorseDetail {...props} />);

			// Assert
			expect(screen.getByText("東京優駿")).toBeInTheDocument();
		});
	});
});
