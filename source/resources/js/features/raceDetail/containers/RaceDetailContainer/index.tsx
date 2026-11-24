import { useState } from "react";
import { toast } from "sonner";
import EntryCommentModalContainer from "@/features/entryComment/containers/EntryCommentModalContainer";
import HorseNoteModalContainer from "@/features/horseNote/containers/HorseNoteModalContainer";
import type { HorseNote } from "@/features/horseNote/types/horseNote";
import { useEntryComments } from "@/features/raceDetail/hooks/useEntryComments";
import { useRaceDetailModals } from "@/features/raceDetail/hooks/useRaceDetailModals";
import { useRaceMarkColumns } from "@/features/raceDetail/hooks/useRaceMarkColumns";
import { useRaceMarkMemos } from "@/features/raceDetail/hooks/useRaceMarkMemos";
import { useRaceMarks } from "@/features/raceDetail/hooks/useRaceMarks";
import { useTrainerNotes } from "@/features/raceDetail/hooks/useTrainerNotes";
import RaceDetail from "@/features/raceDetail/presentational/RaceDetail";
import type {
	RaceDetailItem,
	RaceEntry,
} from "@/features/raceDetail/presentational/RaceDetail/types";
import { deleteColumn } from "@/features/raceDetail/requests/raceMarkColumns";
import RaceMarkMemoModalContainer from "@/features/raceMarkMemo/containers/RaceMarkMemoModalContainer";
import type { RaceMarkMemoTarget } from "@/features/raceMarkMemo/requests/raceMarkMemos";
import RaceNominationSectionContainer from "@/features/raceNomination/containers/RaceNominationSectionContainer";
import { useRaceNominationColumnMarks } from "@/features/raceNomination/hooks/useRaceNominationColumnMarks";
import { useRaceNominationColumnMemos } from "@/features/raceNomination/hooks/useRaceNominationColumnMemos";
import { useRaceNominations } from "@/features/raceNomination/hooks/useRaceNominations";
import TrainerNoteModalContainer from "@/features/trainerNote/containers/TrainerNoteModalContainer";
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
	const nominationsHook = useRaceNominations(race.uid, race.nominations ?? []);
	const nominationMarksHook = useRaceNominationColumnMarks(
		race.uid,
		race.nomination_marks ?? [],
	);
	const nominationMemosHook = useRaceNominationColumnMemos(
		race.nomination_mark_memos ?? [],
	);
	const modals = useRaceDetailModals({
		entries,
		markColumns: columnsHook.markColumns,
		marks: marksHook.marks,
		markMemos: memosHook.markMemos,
		nominations: nominationsHook.nominations,
		nominationMarks: nominationMarksHook.marks,
		nominationMarkMemos: nominationMemosHook.markMemos,
	});
	const trainerNotes = useTrainerNotes(entries, setEntries);
	const entryComments = useEntryComments(entries, setEntries);

	const handleRemoveOtherColumn = async (columnId: number) => {
		columnsHook.cancelLabelDebounce(columnId);
		const colSnap = columnsHook.removeColumnLocally(columnId);
		const markSnap = marksHook.removeByColumnLocally(columnId);
		const memoSnap = memosHook.removeByColumnLocally(columnId);
		const nominationMarkSnap =
			nominationMarksHook.removeByColumnLocally(columnId);
		const nominationMemoSnap =
			nominationMemosHook.removeByColumnLocally(columnId);
		try {
			await deleteColumn(race.uid, columnId);
		} catch (_e) {
			columnsHook.restoreColumns(colSnap);
			marksHook.restoreSnapshot(markSnap);
			memosHook.restoreSnapshot(memoSnap);
			nominationMarksHook.restoreSnapshot(nominationMarkSnap);
			nominationMemosHook.restoreSnapshot(nominationMemoSnap);
			toast.error("印列の削除に失敗しました");
		}
	};

	const handleMemoSaved = (params: {
		columnId: number;
		target: RaceMarkMemoTarget;
		content: string;
	}) => {
		const { columnId, target, content } = params;
		if (target.type === "entry") {
			memosHook.handleMemoSaved({
				columnId,
				raceEntryId: target.raceEntryId,
				content,
			});
			return;
		}
		nominationMemosHook.handleMemoSaved({
			columnId,
			raceNominationId: target.raceNominationId,
			content,
		});
	};

	const handleMemoDeleted = (params: {
		columnId: number;
		target: RaceMarkMemoTarget;
	}) => {
		const { columnId, target } = params;
		if (target.type === "entry") {
			memosHook.handleMemoDeleted({
				columnId,
				raceEntryId: target.raceEntryId,
			});
			return;
		}
		nominationMemosHook.handleMemoDeleted({
			columnId,
			raceNominationId: target.raceNominationId,
		});
	};

	const handleNoteSuccess = (note: HorseNote) => {
		nominationsHook.applyNote(note.horse_id, {
			id: note.id,
			content: note.content,
			source: note.race_id != null ? "race" : "horse",
		});
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
		nominations: nominationsHook.nominations,
		nomination_marks: nominationMarksHook.marks,
		nomination_mark_memos: nominationMemosHook.markMemos,
	};

	return (
		<>
			<RaceDetail
				race={localRace}
				nominationStatusByHorseId={nominationsHook.statusByHorseId}
				onMarkChange={marksHook.handleMarkChange}
				onAddOtherColumn={columnsHook.handleAddOtherColumn}
				onRemoveOtherColumn={handleRemoveOtherColumn}
				onChangeColumnLabel={columnsHook.handleChangeColumnLabel}
				onNoteClick={modals.handleNoteClick}
				onTrainerNoteClick={trainerNotes.handleTrainerNoteClick}
				onCommentClick={entryComments.handleCommentClick}
				onMarkMemoClick={modals.handleMarkMemoClick}
			/>
			<div className="mt-6">
				<RaceNominationSectionContainer
					nominations={nominationsHook.nominations}
					onAdd={nominationsHook.addNomination}
					onRemove={nominationsHook.removeNomination}
					onStatusChange={nominationsHook.changeStatus}
					onNoteClick={modals.handleNoteClick}
					markColumns={columnsHook.markColumns}
					marks={nominationMarksHook.marks}
					markMemos={nominationMemosHook.markMemos}
					onAddOtherColumn={columnsHook.handleAddOtherColumn}
					onChangeColumnLabel={columnsHook.handleChangeColumnLabel}
					onRemoveOtherColumn={handleRemoveOtherColumn}
					onMarkChange={nominationMarksHook.handleMarkChange}
					onMarkMemoClick={modals.handleNominationMarkMemoClick}
				/>
			</div>
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
				modals.memoModal.target != null &&
				modals.memoTargetColumn != null &&
				modals.memoTargetHorseName != null && (
					<RaceMarkMemoModalContainer
						open={modals.memoModal.open}
						mode={modals.memoTargetContent != null ? "edit" : "create"}
						raceUid={race.uid}
						columnId={modals.memoModal.columnId}
						target={modals.memoModal.target}
						horseName={modals.memoTargetHorseName}
						columnLabel={modals.memoTargetColumn.label ?? ""}
						markValue={modals.memoTargetMarkValue}
						initialContent={modals.memoTargetContent ?? ""}
						onClose={modals.handleMemoClose}
						onSaved={handleMemoSaved}
						onDeleted={handleMemoDeleted}
					/>
				)}
			{trainerNotes.trainerNoteModal.trainerId != null &&
				trainerNotes.trainerNoteTargetEntry != null && (
					<TrainerNoteModalContainer
						open={trainerNotes.trainerNoteModal.open}
						mode={
							trainerNotes.trainerNoteTargetEntry.trainer_note != null
								? "edit"
								: "create"
						}
						trainerId={trainerNotes.trainerNoteModal.trainerId}
						trainerName={trainerNotes.trainerNoteTargetEntry.trainer_name ?? ""}
						trainerAffiliation={
							trainerNotes.trainerNoteTargetEntry.trainer_affiliation ?? null
						}
						initialContent={
							trainerNotes.trainerNoteTargetEntry.trainer_note?.content ?? ""
						}
						onClose={trainerNotes.handleTrainerNoteClose}
						onSaved={trainerNotes.handleTrainerNoteSaved}
						onDeleted={trainerNotes.handleTrainerNoteDeleted}
					/>
				)}
			{entryComments.commentModal.raceEntryId != null &&
				entryComments.commentTargetEntry != null && (
					<EntryCommentModalContainer
						open={entryComments.commentModal.open}
						raceUid={race.uid}
						raceEntryId={entryComments.commentModal.raceEntryId}
						horseName={entryComments.commentTargetEntry.horse_name}
						trainerName={
							entryComments.commentTargetEntry.trainer_id != null
								? (entryComments.commentTargetEntry.trainer_name ?? null)
								: null
						}
						jockeyName={entryComments.commentTargetEntry.jockey_name}
						comments={entryComments.commentTargetEntry.comments ?? []}
						onClose={entryComments.handleCommentClose}
						onCommentsChange={entryComments.handleCommentsChange}
					/>
				)}
		</>
	);
};

export default RaceDetailContainer;
