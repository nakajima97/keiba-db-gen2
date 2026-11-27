import { useMemo, useState } from "react";
import type { RaceMarkMemoTarget } from "@/features/raceMarkMemo/requests/raceMarkMemos";
import type {
	RaceNomination,
	RaceNominationColumnMarkValue,
	RaceNominationColumnMemo,
	RaceNominationMarkColumn,
} from "@/features/raceNomination/types/raceNomination";
import type {
	MarkValue,
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
	target: RaceMarkMemoTarget | null;
};

type UseRaceDetailModalsParams = {
	entries: ReadonlyArray<RaceEntry>;
	markColumns: ReadonlyArray<RaceMarkColumn>;
	marks: ReadonlyArray<RaceMarkValue>;
	markMemos: ReadonlyArray<RaceMarkMemo>;
	nominations?: ReadonlyArray<RaceNomination>;
	nominationMarkColumns?: ReadonlyArray<RaceNominationMarkColumn>;
	nominationMarks?: ReadonlyArray<RaceNominationColumnMarkValue>;
	nominationMarkMemos?: ReadonlyArray<RaceNominationColumnMemo>;
};

type UseRaceDetailModalsReturn = {
	noteModal: NoteModalState;
	selectedEntry: RaceEntry | undefined;
	selectedNote: RaceEntryNote | null;
	handleNoteClick: (horseId: number) => void;
	handleNoteClose: () => void;

	memoModal: MemoModalState;
	/** 印メモ対象の印列。出走馬なら出馬表の印列、登録馬なら登録馬用の印列から引く。 */
	memoTargetColumn: RaceMarkColumn | RaceNominationMarkColumn | undefined;
	/** 印メモ対象（出走馬または登録馬）の馬名。対象が見つからない場合は undefined。 */
	memoTargetHorseName: string | undefined;
	memoTargetContent: string | undefined;
	memoTargetMarkValue: MarkValue | null;
	handleMarkMemoClick: (params: {
		columnId: number;
		raceEntryId: number;
	}) => void;
	handleNominationMarkMemoClick: (params: {
		columnId: number;
		raceNominationId: number;
	}) => void;
	handleMemoClose: () => void;
};

/**
 * レース詳細画面の馬メモ・印メモモーダル開閉 state と、モーダル props 計算用の派生 state を管理する。
 * 派生 state は entries / markColumns / marks / markMemos から useMemo で導出するため、関係ない再レンダーで再計算されない。
 * 馬メモは出走馬に加えて登録馬（nominations）からも開ける。同じ馬が両方にいる場合は出走馬のメモを優先する。
 * 印メモは出走馬の印・登録馬の他人の印の両方から開け、対象（target）で参照先のデータを切り替える。
 */
export const useRaceDetailModals = (
	params: UseRaceDetailModalsParams,
): UseRaceDetailModalsReturn => {
	const {
		entries,
		markColumns,
		marks,
		markMemos,
		nominations = [],
		nominationMarkColumns = [],
		nominationMarks = [],
		nominationMarkMemos = [],
	} = params;

	const [noteModal, setNoteModal] = useState<NoteModalState>({
		open: false,
		horseId: null,
		horseName: "",
	});
	const [memoModal, setMemoModal] = useState<MemoModalState>({
		open: false,
		columnId: null,
		target: null,
	});

	const handleNoteClick = (horseId: number) => {
		const target =
			entries.find((e) => e.horse_id === horseId) ??
			nominations.find((n) => n.horse_id === horseId);
		if (target == null) {
			return;
		}
		setNoteModal({
			open: true,
			horseId: target.horse_id,
			horseName: target.horse_name,
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
			target: { type: "entry", raceEntryId: params.raceEntryId },
		});
	};

	const handleNominationMarkMemoClick = (params: {
		columnId: number;
		raceNominationId: number;
	}) => {
		setMemoModal({
			open: true,
			columnId: params.columnId,
			target: { type: "nomination", raceNominationId: params.raceNominationId },
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
	const selectedNominationNote = useMemo(
		() =>
			noteModal.horseId != null
				? (nominations.find((n) => n.horse_id === noteModal.horseId)?.note ??
					null)
				: null,
		[nominations, noteModal.horseId],
	);
	const selectedNote: RaceEntryNote | null =
		selectedEntry?.note ?? selectedNominationNote;

	const memoTargetColumn = useMemo(() => {
		const { columnId, target } = memoModal;
		if (columnId == null || target == null) {
			return undefined;
		}
		const columns: ReadonlyArray<RaceMarkColumn | RaceNominationMarkColumn> =
			target.type === "entry" ? markColumns : nominationMarkColumns;
		return columns.find((c) => c.id === columnId);
	}, [markColumns, nominationMarkColumns, memoModal]);
	const memoTarget = useMemo(() => {
		const { columnId, target } = memoModal;
		if (columnId == null || target == null) {
			return undefined;
		}
		if (target.type === "entry") {
			const entry = entries.find((e) => e.id === target.raceEntryId);
			return {
				horseName: entry?.horse_name,
				content: markMemos.find(
					(m) =>
						m.column_id === columnId && m.race_entry_id === target.raceEntryId,
				)?.content,
				markValue:
					marks.find(
						(m) =>
							m.column_id === columnId &&
							m.race_entry_id === target.raceEntryId,
					)?.mark_value ?? null,
			};
		}
		const nomination = nominations.find(
			(n) => n.id === target.raceNominationId,
		);
		return {
			horseName: nomination?.horse_name,
			content: nominationMarkMemos.find(
				(m) =>
					m.column_id === columnId &&
					m.race_nomination_id === target.raceNominationId,
			)?.content,
			markValue:
				nominationMarks.find(
					(m) =>
						m.column_id === columnId &&
						m.race_nomination_id === target.raceNominationId,
				)?.mark_value ?? null,
		};
	}, [
		memoModal,
		entries,
		marks,
		markMemos,
		nominations,
		nominationMarks,
		nominationMarkMemos,
	]);

	return {
		noteModal,
		selectedEntry,
		selectedNote,
		handleNoteClick,
		handleNoteClose,
		memoModal,
		memoTargetColumn,
		memoTargetHorseName: memoTarget?.horseName,
		memoTargetContent: memoTarget?.content,
		memoTargetMarkValue: memoTarget?.markValue ?? null,
		handleMarkMemoClick,
		handleNominationMarkMemoClick,
		handleMemoClose,
	};
};
