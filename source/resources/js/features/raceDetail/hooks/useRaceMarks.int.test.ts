import { act, renderHook } from "@testing-library/react";
import { beforeEach, describe, expect, it, vi } from "vitest";

vi.mock("@/features/raceDetail/requests/raceMarks", () => ({
	upsertMark: vi.fn(),
}));
vi.mock("sonner", () => ({
	toast: { error: vi.fn() },
}));

import { upsertMark } from "@/features/raceDetail/requests/raceMarks";
import { useRaceMarks } from "./useRaceMarks";

describe("useRaceMarks", () => {
	beforeEach(() => {
		vi.clearAllMocks();
	});

	it("ハッピーパス: handleMarkChange を呼ぶと upsertMark が正しい引数で呼ばれ、marks に新しい印が追加されること", async () => {
		// Arrange
		vi.mocked(upsertMark).mockResolvedValue(null);
		const { result } = renderHook(() => useRaceMarks("race-uid", []));

		// Act
		await act(async () => {
			await result.current.handleMarkChange({
				columnId: 10,
				raceEntryId: 20,
				markValue: "◎",
			});
		});

		// Assert
		expect(upsertMark).toHaveBeenCalledWith("race-uid", 10, 20, "◎");
		expect(result.current.marks).toEqual([
			{ column_id: 10, race_entry_id: 20, mark_value: "◎" },
		]);
	});
});
