import { describe, it, expect } from "vitest";
import { formatDateDisplay } from "@/utils/date";

describe("formatDateDisplay", () => {
	it("ハイフン区切りの日付をスラッシュ区切りに変換する", () => {
		// Arrange
		const input = "2026-04-25";

		// Act
		const result = formatDateDisplay(input);

		// Assert
		expect(result).toBe("2026/04/25");
	});

	it("月初・年始の日付をスラッシュ区切りに変換する", () => {
		// Arrange
		const input = "2026-01-01";

		// Act
		const result = formatDateDisplay(input);

		// Assert
		expect(result).toBe("2026/01/01");
	});

	it("月末・年末の日付をスラッシュ区切りに変換する", () => {
		// Arrange
		const input = "2026-12-31";

		// Act
		const result = formatDateDisplay(input);

		// Assert
		expect(result).toBe("2026/12/31");
	});
});
