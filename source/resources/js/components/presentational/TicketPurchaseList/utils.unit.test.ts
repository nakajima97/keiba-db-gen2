import { describe, it, expect } from "vitest";
import { formatSelections } from "./utils";

describe("formatSelections", () => {
	describe("horses形式（single/box）", () => {
		it("{horses: [1, 3, 5]} のとき '1-3-5' を返す", () => {
			// Act
			const result = formatSelections({ horses: [1, 3, 5] });

			// Assert
			expect(result).toBe("1-3-5");
		});
	});

	describe("axis/others形式（nagashi）", () => {
		it("{axis: [1], others: [2, 4, 6]} のとき '1 - 2, 4, 6' を返す", () => {
			// Act
			const result = formatSelections({ axis: [1], others: [2, 4, 6] });

			// Assert
			expect(result).toBe("1 - 2, 4, 6");
		});

		it("{axis: [3, 5], others: [1, 7]} のとき '3, 5 - 1, 7' を返す", () => {
			// Act
			const result = formatSelections({ axis: [3, 5], others: [1, 7] });

			// Assert
			expect(result).toBe("3, 5 - 1, 7");
		});
	});

	describe("columns形式（formation）", () => {
		it("{columns: [[1, 2], [3, 4], [5, 6, 7]]} のとき '1,2 - 3,4 - 5,6,7' を返す", () => {
			// Act
			const result = formatSelections({
				columns: [
					[1, 2],
					[3, 4],
					[5, 6, 7],
				],
			});

			// Assert
			expect(result).toBe("1,2 - 3,4 - 5,6,7");
		});
	});
});
