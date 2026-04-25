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

	describe("axis/others形式（nagashi1軸）", () => {
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

		it("{axis: [1]} のとき（othersなし）'1' を返す", () => {
			// Act
			const result = formatSelections({ axis: [1] });

			// Assert
			expect(result).toBe("1");
		});
	});

	describe("axis1/axis2/others形式（nagashi2軸）", () => {
		it("{axis1: [1], axis2: [3], others: [4, 5, 6]} のとき '1 / 3 - 4, 5, 6' を返す", () => {
			// Act
			const result = formatSelections({
				axis1: [1],
				axis2: [3],
				others: [4, 5, 6],
			});

			// Assert
			expect(result).toBe("1 / 3 - 4, 5, 6");
		});

		it("{axis1: [1]} のとき（axis2/othersなし）'1' を返す", () => {
			// Act
			const result = formatSelections({ axis1: [1] });

			// Assert
			expect(result).toBe("1");
		});
	});

	describe("col1/col2/col3形式（formation）", () => {
		it("{col1: [1, 2], col2: [3, 4], col3: [5, 6, 7]} のとき '1,2 - 3,4 - 5,6,7' を返す", () => {
			// Act
			const result = formatSelections({
				col1: [1, 2],
				col2: [3, 4],
				col3: [5, 6, 7],
			});

			// Assert
			expect(result).toBe("1,2 - 3,4 - 5,6,7");
		});

		it("{col1: [1, 2]} のとき（col2/col3なし）'1,2' を返す", () => {
			// Act
			const result = formatSelections({ col1: [1, 2] });

			// Assert
			expect(result).toBe("1,2");
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
