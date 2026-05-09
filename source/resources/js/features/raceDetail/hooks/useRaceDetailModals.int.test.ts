import { act, renderHook } from "@testing-library/react";
import { describe, expect, it } from "vitest";
import type {
	RaceEntry,
	RaceMarkColumn,
	RaceMarkMemo,
	RaceMarkValue,
} from "@/features/raceDetail/presentational/RaceDetail/types";
import { useRaceDetailModals } from "./useRaceDetailModals";

const entry: RaceEntry = {
	id: 1,
	uid: "entry-uid-1",
	frame_number: 2,
	horse_number: 3,
	horse_id: 42,
	horse_uid: "horse-uid-42",
	horse_name: "テストホース",
	jockey_name: "テスト騎手",
	weight: 480,
};

describe("useRaceDetailModals", () => {
	it("ハッピーパス: handleNoteClick を呼ぶと noteModal が open になり、selectedEntry が該当 entry を返すこと", () => {
		// Arrange
		const entries: RaceEntry[] = [entry];
		const markColumns: RaceMarkColumn[] = [];
		const marks: RaceMarkValue[] = [];
		const markMemos: RaceMarkMemo[] = [];
		const { result } = renderHook(() =>
			useRaceDetailModals({ entries, markColumns, marks, markMemos }),
		);

		// Act
		act(() => {
			result.current.handleNoteClick(42);
		});

		// Assert
		expect(result.current.noteModal).toEqual({
			open: true,
			horseId: 42,
			horseName: "テストホース",
		});
		expect(result.current.selectedEntry).toBe(entry);
	});
});
