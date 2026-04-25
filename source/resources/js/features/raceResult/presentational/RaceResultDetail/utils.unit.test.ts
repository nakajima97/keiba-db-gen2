import { describe, it, expect } from "vitest";
import { formatHorseNumbers } from "./utils";

describe("formatHorseNumbers", () => {
	it("馬番が1頭の場合（単勝など）、馬番をそのまま返す", () => {
		// Act
		const result = formatHorseNumbers(
			[{ horse_number: 3, sort_order: 1 }],
			"tansho",
		);

		// Assert
		expect(result).toBe("3");
	});

	it("2頭・ハイフン表記（馬連など）", () => {
		// Act
		const result = formatHorseNumbers(
			[
				{ horse_number: 3, sort_order: 1 },
				{ horse_number: 5, sort_order: 2 },
			],
			"umaren",
		);

		// Assert
		expect(result).toBe("3-5");
	});

	it("3頭・ハイフン表記（三連複など）", () => {
		// Act
		const result = formatHorseNumbers(
			[
				{ horse_number: 3, sort_order: 1 },
				{ horse_number: 5, sort_order: 2 },
				{ horse_number: 8, sort_order: 3 },
			],
			"sanrenpuku",
		);

		// Assert
		expect(result).toBe("3-5-8");
	});

	it("2頭・矢印表記（馬単）", () => {
		// Act
		const result = formatHorseNumbers(
			[
				{ horse_number: 3, sort_order: 1 },
				{ horse_number: 5, sort_order: 2 },
			],
			"umatan",
		);

		// Assert
		expect(result).toBe("3→5");
	});

	it("3頭・矢印表記（三連単）", () => {
		// Act
		const result = formatHorseNumbers(
			[
				{ horse_number: 3, sort_order: 1 },
				{ horse_number: 5, sort_order: 2 },
				{ horse_number: 8, sort_order: 3 },
			],
			"sanrentan",
		);

		// Assert
		expect(result).toBe("3→5→8");
	});

	it("sort_order が逆順で渡されても正しく並び替えられる", () => {
		// Act
		const result = formatHorseNumbers(
			[
				{ horse_number: 8, sort_order: 3 },
				{ horse_number: 3, sort_order: 1 },
				{ horse_number: 5, sort_order: 2 },
			],
			"sanrentan",
		);

		// Assert
		expect(result).toBe("3→5→8");
	});
});
