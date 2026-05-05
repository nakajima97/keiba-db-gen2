import { act, renderHook } from "@testing-library/react";
import { describe, expect, it } from "vitest";
import { useRaceMarkMemos } from "./useRaceMarkMemos";

describe("useRaceMarkMemos", () => {
	it("ハッピーパス: handleMemoSaved を呼ぶと該当 (columnId, raceEntryId) のメモが markMemos に upsert されること", () => {
		// Arrange
		const { result } = renderHook(() => useRaceMarkMemos([]));

		// Act
		act(() => {
			result.current.handleMemoSaved({
				columnId: 10,
				raceEntryId: 20,
				content: "メモ本文",
			});
		});

		// Assert
		expect(result.current.markMemos).toEqual([
			{ column_id: 10, race_entry_id: 20, content: "メモ本文" },
		]);
	});
});
