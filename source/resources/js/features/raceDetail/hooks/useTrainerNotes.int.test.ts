import { act, renderHook } from "@testing-library/react";
import { useState } from "react";
import { describe, expect, it } from "vitest";
import type { RaceEntry } from "@/features/raceDetail/presentational/RaceDetail/types";
import { useTrainerNotes } from "./useTrainerNotes";

const buildEntry = (overrides: Partial<RaceEntry>): RaceEntry => ({
	id: 1,
	uid: "entry-uid-1",
	frame_number: 1,
	horse_number: 1,
	horse_id: 100,
	horse_uid: "horse-uid-100",
	horse_name: "テストホース",
	jockey_name: "テスト騎手",
	trainer_id: 10,
	trainer_name: "テスト調教師",
	trainer_affiliation: "美浦",
	trainer_note: null,
	weight: 480,
	...overrides,
});

const initialEntries: RaceEntry[] = [
	buildEntry({ id: 1, horse_number: 1, trainer_id: 10 }),
	buildEntry({ id: 2, horse_number: 2, trainer_id: 10 }),
	buildEntry({
		id: 3,
		horse_number: 3,
		trainer_id: 20,
		trainer_name: "別の調教師",
		trainer_note: { id: 99, content: "既存メモ" },
	}),
];

const renderUseTrainerNotes = () =>
	renderHook(() => {
		const [entries, setEntries] = useState<RaceEntry[]>(initialEntries);
		return { entries, ...useTrainerNotes(entries, setEntries) };
	});

describe("useTrainerNotes", () => {
	it("handleTrainerNoteClick でモーダルが開き、対象調教師の出走馬が trainerNoteTargetEntry になる", () => {
		// Arrange
		const { result } = renderUseTrainerNotes();

		// Act
		act(() => {
			result.current.handleTrainerNoteClick(20);
		});

		// Assert
		expect(result.current.trainerNoteModal).toEqual({
			open: true,
			trainerId: 20,
		});
		expect(result.current.trainerNoteTargetEntry?.trainer_name).toBe(
			"別の調教師",
		);
	});

	it("handleTrainerNoteSaved を呼ぶと同じ調教師の全出走馬に trainer_note が反映される", () => {
		// Arrange
		const { result } = renderUseTrainerNotes();

		// Act
		act(() => {
			result.current.handleTrainerNoteSaved({
				trainerId: 10,
				id: 5,
				content: "コメントは控えめ",
			});
		});

		// Assert
		expect(result.current.entries[0].trainer_note).toEqual({
			id: 5,
			content: "コメントは控えめ",
		});
		expect(result.current.entries[1].trainer_note).toEqual({
			id: 5,
			content: "コメントは控えめ",
		});
		expect(result.current.entries[2].trainer_note).toEqual({
			id: 99,
			content: "既存メモ",
		});
	});

	it("handleTrainerNoteDeleted を呼ぶと該当調教師の出走馬の trainer_note が null になる", () => {
		// Arrange
		const { result } = renderUseTrainerNotes();

		// Act
		act(() => {
			result.current.handleTrainerNoteDeleted({ trainerId: 20 });
		});

		// Assert
		expect(result.current.entries[2].trainer_note).toBeNull();
	});
});
