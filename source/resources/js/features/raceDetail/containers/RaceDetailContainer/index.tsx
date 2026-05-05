import { useState } from "react";
import { toast } from "sonner";
import HorseNoteModalContainer from "@/features/horseNote/containers/HorseNoteModalContainer";
import type { HorseNote } from "@/features/horseNote/types/horseNote";
import { useRaceDetailModals } from "@/features/raceDetail/hooks/useRaceDetailModals";
import { useRaceMarkColumns } from "@/features/raceDetail/hooks/useRaceMarkColumns";
import { useRaceMarkMemos } from "@/features/raceDetail/hooks/useRaceMarkMemos";
import { useRaceMarks } from "@/features/raceDetail/hooks/useRaceMarks";
import RaceDetail from "@/features/raceDetail/presentational/RaceDetail";
import type {
	RaceDetailItem,
	RaceEntry,
} from "@/features/raceDetail/presentational/RaceDetail/types";
import { deleteColumn } from "@/features/raceDetail/requests/raceMarkColumns";
import RaceMarkMemoModalContainer from "@/features/raceMarkMemo/containers/RaceMarkMemoModalContainer";
import { formatDateDisplay } from "@/utils/date";

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

const RaceDetailContainer = ({ race }: Props) => {
	const [entries, setEntries] = useState<RaceEntry[]>(race.entries);
	const columnsHook = useRaceMarkColumns(race.uid, race.mark_columns);
	const marksHook = useRaceMarks(race.uid, race.marks);
	const memosHook = useRaceMarkMemos(race.mark_memos ?? []);
	const modals = useRaceDetailModals({
		entries,
		markColumns: columnsHook.markColumns,
		marks: marksHook.marks,
		markMemos: memosHook.markMemos,
	});

	const handleRemoveOtherColumn = async (columnId: number) => {
		columnsHook.cancelLabelDebounce(columnId);
		const colSnap = columnsHook.removeColumnLocally(columnId);
		const markSnap = marksHook.removeByColumnLocally(columnId);
		const memoSnap = memosHook.removeByColumnLocally(columnId);
		try {
			await deleteColumn(race.uid, columnId);
		} catch (_e) {
			columnsHook.restoreColumns(colSnap);
			marksHook.restoreSnapshot(markSnap);
			memosHook.restoreSnapshot(memoSnap);
			toast.error("印列の削除に失敗しました");
		}
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

	const localRace: RaceDetailItem = {
		...race,
		entries,
		mark_columns: columnsHook.markColumns,
		marks: marksHook.marks,
		mark_memos: memosHook.markMemos,
	};

	return (
		<>
			<RaceDetail
				race={localRace}
				onMarkChange={marksHook.handleMarkChange}
				onAddOtherColumn={columnsHook.handleAddOtherColumn}
				onRemoveOtherColumn={handleRemoveOtherColumn}
				onChangeColumnLabel={columnsHook.handleChangeColumnLabel}
				onNoteClick={modals.handleNoteClick}
				onMarkMemoClick={modals.handleMarkMemoClick}
			/>
			{modals.noteModal.horseId != null && (
				<HorseNoteModalContainer
					open={modals.noteModal.open}
					mode={modals.selectedNote != null ? "edit" : "create"}
					horseId={modals.noteModal.horseId}
					horseName={modals.noteModal.horseName}
					noteId={modals.selectedNote?.id}
					initialContent={modals.selectedNote?.content ?? ""}
					raceId={modals.selectedNote != null ? null : (race.id ?? null)}
					raceContext={
						modals.selectedNote != null && modals.selectedNote.source === "horse"
							? { type: "none" }
							: { type: "fixed", label: buildRaceLabel(race) }
					}
					onClose={modals.handleNoteClose}
					onSuccess={handleNoteSuccess}
				/>
			)}
			{modals.memoModal.columnId != null &&
				modals.memoModal.raceEntryId != null &&
				modals.memoTargetColumn != null &&
				modals.memoTargetEntry != null && (
					<RaceMarkMemoModalContainer
						open={modals.memoModal.open}
						mode={modals.memoTargetMemo != null ? "edit" : "create"}
						raceUid={race.uid}
						columnId={modals.memoModal.columnId}
						raceEntryId={modals.memoModal.raceEntryId}
						horseName={modals.memoTargetEntry.horse_name}
						columnLabel={modals.memoTargetColumn.label ?? ""}
						markValue={modals.memoTargetMark?.mark_value ?? null}
						initialContent={modals.memoTargetMemo?.content ?? ""}
						onClose={modals.handleMemoClose}
						onSaved={memosHook.handleMemoSaved}
						onDeleted={memosHook.handleMemoDeleted}
					/>
				)}
		</>
	);
};

export default RaceDetailContainer;
