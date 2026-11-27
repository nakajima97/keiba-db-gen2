import { act, renderHook } from "@testing-library/react";
import { describe, expect, it } from "vitest";
import type {
	RaceEntry,
	RaceMarkColumn,
	RaceMarkMemo,
	RaceMarkValue,
} from "@/features/raceDetail/presentational/RaceDetail/types";
import type {
	RaceNomination,
	RaceNominationMarkColumn,
} from "@/features/raceNomination/types/raceNomination";
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

	it("登録馬の印メモを開くと memoTargetColumn は出馬表ではなく登録馬用の印列から引かれる", () => {
		// Arrange
		const markColumns: RaceMarkColumn[] = [
			{ id: 5, type: "other", label: "出馬表の列", display_order: 1 },
		];
		const nominationMarkColumns: RaceNominationMarkColumn[] = [
			{ id: 5, label: "登録馬の列", display_order: 1 },
		];
		const nominations: RaceNomination[] = [
			{
				id: 7,
				horse_id: 43,
				horse_uid: "horse-uid-43",
				horse_name: "登録ホース",
				status: null,
				note: null,
			},
		];
		const { result } = renderHook(() =>
			useRaceDetailModals({
				entries: [entry],
				markColumns,
				marks: [],
				markMemos: [],
				nominations,
				nominationMarkColumns,
			}),
		);

		// Act
		act(() => {
			result.current.handleNominationMarkMemoClick({
				columnId: 5,
				raceNominationId: 7,
			});
		});

		// Assert
		expect(result.current.memoTargetColumn).toBe(nominationMarkColumns[0]);
		expect(result.current.memoTargetHorseName).toBe("登録ホース");
	});
});
