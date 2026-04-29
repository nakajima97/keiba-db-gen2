import { useState } from "react";
import { toast } from "sonner";
import HorseNoteModalContainer from "@/features/horseNote/containers/HorseNoteModalContainer";
import type { HorseNote } from "@/features/horseNote/types/horseNote";
import RaceDetail from "@/features/raceDetail/presentational/RaceDetail";
import type {
	MarkValue,
	RaceDetailItem,
	RaceEntry,
	RaceMarkColumn,
	RaceMarkMemo,
	RaceMarkValue,
} from "@/features/raceDetail/presentational/RaceDetail/types";
import {
	createOtherColumn,
	deleteColumn,
	updateColumnLabel,
} from "@/features/raceDetail/requests/raceMarkColumns";
import { upsertMark } from "@/features/raceDetail/requests/raceMarks";
import RaceMarkMemoModalContainer from "@/features/raceMarkMemo/containers/RaceMarkMemoModalContainer";
import { useDebouncedCallbackByKey } from "@/hooks/useDebouncedCallback";
import { formatDateDisplay } from "@/utils/date";

const LABEL_DEBOUNCE_MS = 500;

type Props = {
	race: RaceDetailItem & {
		id?: number;
		race_name?: string | null;
	};
};

const buildRaceLabel = (race: Props["race"]): string => {
	const base = `${formatDateDisplay(race.race_date)} ${race.venue_name} ${race.race_number}R`;
	return race.race_name != null ? `${base} ${race.race_name}` : base;
};

/**
 * レース詳細画面のコンテナ。
 * 印列・印データ・印メモのローカル state を保持し、ユーザー操作で楽観的に更新→API 呼び出し。
 * API 失敗時は state を元に戻して toast でエラーを通知する。
 * ラベル編集はキー入力中の連打を避けるため列ごとに 500ms デバウンスする。
 */
const RaceDetailContainer = ({ race }: Props) => {
	const [markColumns, setMarkColumns] = useState<RaceMarkColumn[]>(
		race.mark_columns,
	);
	const [marks, setMarks] = useState<RaceMarkValue[]>(race.marks);
	const [markMemos, setMarkMemos] = useState<RaceMarkMemo[]>(
		race.mark_memos ?? [],
	);
	const [entries, setEntries] = useState<RaceEntry[]>(race.entries);
	const [noteModal, setNoteModal] = useState<{
		open: boolean;
		horseId: number | null;
		horseName: string;
	}>({ open: false, horseId: null, horseName: "" });
	const [memoModal, setMemoModal] = useState<{
		open: boolean;
		columnId: number | null;
		raceEntryId: number | null;
	}>({ open: false, columnId: null, raceEntryId: null });

	const labelDebouncer = useDebouncedCallbackByKey(
		async (raceUid: string, columnId: number, label: string) => {
			try {
				await updateColumnLabel(raceUid, columnId, label);
			} catch (_e) {
				toast.error("ラベルの更新に失敗しました");
			}
		},
		LABEL_DEBOUNCE_MS,
	);

	const localRace: RaceDetailItem = {
		...race,
		entries,
		mark_columns: markColumns,
		marks,
		mark_memos: markMemos,
	};

	const handleAddOtherColumn = async () => {
		const tempId = -Date.now();
		const maxOrder = markColumns.reduce(
			(acc, c) => (c.display_order > acc ? c.display_order : acc),
			0,
		);
		const optimistic: RaceMarkColumn = {
			id: tempId,
			type: "other",
			label: "",
			display_order: maxOrder + 1,
		};
		const previous = markColumns;
		setMarkColumns([...previous, optimistic]);
		try {
			const created = await createOtherColumn(race.uid, "");
			setMarkColumns((current) =>
				current.map((c) => (c.id === tempId ? created : c)),
			);
		} catch (_e) {
			setMarkColumns(previous);
			toast.error("印列の追加に失敗しました");
		}
	};

	const handleRemoveOtherColumn = async (columnId: number) => {
		labelDebouncer.cancel(columnId);
		const previousColumns = markColumns;
		const previousMarks = marks;
		const previousMemos = markMemos;
		setMarkColumns((current) => current.filter((c) => c.id !== columnId));
		setMarks((current) => current.filter((m) => m.column_id !== columnId));
		setMarkMemos((current) => current.filter((m) => m.column_id !== columnId));
		try {
			await deleteColumn(race.uid, columnId);
		} catch (_e) {
			setMarkColumns(previousColumns);
			setMarks(previousMarks);
			setMarkMemos(previousMemos);
			toast.error("印列の削除に失敗しました");
		}
	};

	const handleChangeColumnLabel = (columnId: number, label: string) => {
		setMarkColumns((current) =>
			current.map((c) => (c.id === columnId ? { ...c, label } : c)),
		);
		labelDebouncer.call(columnId, race.uid, columnId, label);
	};

	const handleMarkChange = async (params: {
		columnId: number;
		raceEntryId: number;
		markValue: MarkValue | null;
	}) => {
		const { columnId, raceEntryId, markValue } = params;
		const previous = marks;
		const filtered = marks.filter(
			(m) =>
				!(m.column_id === columnId && m.race_entry_id === raceEntryId),
		);
		const next: RaceMarkValue[] =
			markValue === null
				? filtered
				: [
						...filtered,
						{
							column_id: columnId,
							race_entry_id: raceEntryId,
							mark_value: markValue,
						},
					];
		setMarks(next);
		try {
			await upsertMark(race.uid, columnId, raceEntryId, markValue);
		} catch (_e) {
			setMarks(previous);
			toast.error("印の更新に失敗しました");
		}
	};

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

	const handleNoteSuccess = (note: HorseNote) => {
		setEntries((current) =>
			current.map((entry) => {
				if (entry.horse_id !== note.horse_id) {
					return entry;
				}
				return {
					...entry,
					note: {
						id: note.id,
						content: note.content,
						source: note.race_id != null ? "race" : "horse",
					},
				};
			}),
		);
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

	const handleMemoSaved = (params: {
		columnId: number;
		raceEntryId: number;
		content: string;
	}) => {
		setMarkMemos((current) => {
			const filtered = current.filter(
				(m) =>
					!(
						m.column_id === params.columnId &&
						m.race_entry_id === params.raceEntryId
					),
			);
			return [
				...filtered,
				{
					column_id: params.columnId,
					race_entry_id: params.raceEntryId,
					content: params.content,
				},
			];
		});
	};

	const handleMemoDeleted = (params: {
		columnId: number;
		raceEntryId: number;
	}) => {
		setMarkMemos((current) =>
			current.filter(
				(m) =>
					!(
						m.column_id === params.columnId &&
						m.race_entry_id === params.raceEntryId
					),
			),
		);
	};

	const selectedEntry =
		noteModal.horseId != null
			? entries.find((e) => e.horse_id === noteModal.horseId)
			: undefined;
	const selectedNote = selectedEntry?.note ?? null;

	const memoTargetColumn =
		memoModal.columnId != null
			? markColumns.find((c) => c.id === memoModal.columnId)
			: undefined;
	const memoTargetEntry =
		memoModal.raceEntryId != null
			? entries.find((e) => e.id === memoModal.raceEntryId)
			: undefined;
	const memoTargetMemo =
		memoModal.columnId != null && memoModal.raceEntryId != null
			? markMemos.find(
					(m) =>
						m.column_id === memoModal.columnId &&
						m.race_entry_id === memoModal.raceEntryId,
				)
			: undefined;
	const memoTargetMark =
		memoModal.columnId != null && memoModal.raceEntryId != null
			? marks.find(
					(m) =>
						m.column_id === memoModal.columnId &&
						m.race_entry_id === memoModal.raceEntryId,
				)
			: undefined;

	return (
		<>
			<RaceDetail
				race={localRace}
				onMarkChange={handleMarkChange}
				onAddOtherColumn={handleAddOtherColumn}
				onRemoveOtherColumn={handleRemoveOtherColumn}
				onChangeColumnLabel={handleChangeColumnLabel}
				onNoteClick={handleNoteClick}
				onMarkMemoClick={handleMarkMemoClick}
			/>
			{noteModal.horseId != null && (
				<HorseNoteModalContainer
					open={noteModal.open}
					mode={selectedNote != null ? "edit" : "create"}
					horseId={noteModal.horseId}
					horseName={noteModal.horseName}
					noteId={selectedNote?.id}
					initialContent={selectedNote?.content ?? ""}
					raceId={selectedNote != null ? null : (race.id ?? null)}
					raceContext={
						selectedNote != null && selectedNote.source === "horse"
							? { type: "none" }
							: { type: "fixed", label: buildRaceLabel(race) }
					}
					onClose={handleNoteClose}
					onSuccess={handleNoteSuccess}
				/>
			)}
			{memoModal.columnId != null &&
				memoModal.raceEntryId != null &&
				memoTargetColumn != null &&
				memoTargetEntry != null && (
					<RaceMarkMemoModalContainer
						open={memoModal.open}
						mode={memoTargetMemo != null ? "edit" : "create"}
						raceUid={race.uid}
						columnId={memoModal.columnId}
						raceEntryId={memoModal.raceEntryId}
						horseName={memoTargetEntry.horse_name}
						columnLabel={memoTargetColumn.label ?? ""}
						markValue={memoTargetMark?.mark_value ?? null}
						initialContent={memoTargetMemo?.content ?? ""}
						onClose={handleMemoClose}
						onSaved={handleMemoSaved}
						onDeleted={handleMemoDeleted}
					/>
				)}
		</>
	);
};

export default RaceDetailContainer;
