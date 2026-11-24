import { act, renderHook } from "@testing-library/react";
import { describe, expect, it } from "vitest";
import { useRaceNominationColumnMemos } from "./useRaceNominationColumnMemos";

describe("useRaceNominationColumnMemos", () => {
	it("ハッピーパス: handleMemoSaved を呼ぶと該当 (columnId, raceNominationId) のメモが markMemos に upsert されること", () => {
		// Arrange
		const { result } = renderHook(() =>
			useRaceNominationColumnMemos([
				{ column_id: 10, race_nomination_id: 20, content: "旧メモ" },
			]),
		);

		// Act
		act(() => {
			result.current.handleMemoSaved({
				columnId: 10,
				raceNominationId: 20,
				content: "新メモ",
			});
		});

		// Assert
		expect(result.current.markMemos).toEqual([
			{ column_id: 10, race_nomination_id: 20, content: "新メモ" },
		]);
	});
});
