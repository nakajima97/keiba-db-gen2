import { useMemo, useState } from "react";
import type {
	RaceEntry,
	RaceEntryNote,
	RaceMarkColumn,
	RaceMarkMemo,
	RaceMarkValue,
} from "@/features/raceDetail/presentational/RaceDetail/types";

type NoteModalState = {
	open: boolean;
	horseId: number | null;
	horseName: string;
};

type MemoModalState = {
	open: boolean;
	columnId: number | null;
	raceEntryId: number | null;
};

type UseRaceDetailModalsParams = {
	entries: ReadonlyArray<RaceEntry>;
	markColumns: ReadonlyArray<RaceMarkColumn>;
	marks: ReadonlyArray<RaceMarkValue>;
	markMemos: ReadonlyArray<RaceMarkMemo>;
};

type UseRaceDetailModalsReturn = {
	noteModal: NoteModalState;
	selectedEntry: RaceEntry | undefined;
	selectedNote: RaceEntryNote | null;
	handleNoteClick: (horseId: number) => void;
	handleNoteClose: () => void;

	memoModal: MemoModalState;
	memoTargetColumn: RaceMarkColumn | undefined;
	memoTargetEntry: RaceEntry | undefined;
	memoTargetMemo: RaceMarkMemo | undefined;
	memoTargetMark: RaceMarkValue | undefined;
	handleMarkMemoClick: (params: {
		columnId: number;
		raceEntryId: number;
	}) => void;
	handleMemoClose: () => void;
};

/**
 * レース詳細画面の馬メモ・印メモモーダル開閉 state と、モーダル props 計算用の派生 state を管理する。
 * 派生 state は entries / markColumns / marks / markMemos から useMemo で導出するため、関係ない再レンダーで再計算されない。
 */
export const useRaceDetailModals = (
	params: UseRaceDetailModalsParams,
): UseRaceDetailModalsReturn => {
	const { entries, markColumns, marks, markMemos } = params;

	const [noteModal, setNoteModal] = useState<NoteModalState>({
		open: false,
		horseId: null,
		horseName: "",
	});
	const [memoModal, setMemoModal] = useState<MemoModalState>({
		open: false,
		columnId: null,
		raceEntryId: null,
	});

	const handleNoteClick = (horseId: number) => {
		const entry = entries.find((e) => e.horse_id === horseId);
		if (entry == null) {
			return;
		}
		setNoteModal({
			open: true,
			horseId: entry.horse_id,
			horseName: entry.horse_name,
		});
	};

	const handleNoteClose = () => {
		setNoteModal((current) => ({ ...current, open: false }));
	};

	const handleMarkMemoClick = (params: {
		columnId: number;
		raceEntryId: number;
	}) => {
		setMemoModal({
			open: true,
			columnId: params.columnId,
			raceEntryId: params.raceEntryId,
		});
	};

	const handleMemoClose = () => {
		setMemoModal((current) => ({ ...current, open: false }));
	};

	const selectedEntry = useMemo(
		() =>
			noteModal.horseId != null
				? entries.find((e) => e.horse_id === noteModal.horseId)
				: undefined,
		[entries, noteModal.horseId],
	);
	const selectedNote: RaceEntryNote | null = selectedEntry?.note ?? null;

	const memoTargetColumn = useMemo(
		() =>
			memoModal.columnId != null
				? markColumns.find((c) => c.id === memoModal.columnId)
				: undefined,
		[markColumns, memoModal.columnId],
	);
	const memoTargetEntry = useMemo(
		() =>
			memoModal.raceEntryId != null
				? entries.find((e) => e.id === memoModal.raceEntryId)
				: undefined,
		[entries, memoModal.raceEntryId],
	);
	const memoTargetMemo = useMemo(
		() =>
			memoModal.columnId != null && memoModal.raceEntryId != null
				? markMemos.find(
						(m) =>
							m.column_id === memoModal.columnId &&
							m.race_entry_id === memoModal.raceEntryId,
					)
				: undefined,
		[markMemos, memoModal.columnId, memoModal.raceEntryId],
	);
	const memoTargetMark = useMemo(
		() =>
			memoModal.columnId != null && memoModal.raceEntryId != null
				? marks.find(
						(m) =>
							m.column_id === memoModal.columnId &&
							m.race_entry_id === memoModal.raceEntryId,
					)
				: undefined,
		[marks, memoModal.columnId, memoModal.raceEntryId],
	);

	return {
		noteModal,
		selectedEntry,
		selectedNote,
		handleNoteClick,
		handleNoteClose,
		memoModal,
		memoTargetColumn,
		memoTargetEntry,
		memoTargetMemo,
		memoTargetMark,
		handleMarkMemoClick,
		handleMemoClose,
	};
};
