import HorseNoteModalContainer, {
	type HorseNoteModalRaceContext,
} from "@/features/horseNote/containers/HorseNoteModalContainer";
import HorseNoteDeleteConfirmDialog from "@/features/horseNote/presentational/HorseNoteDeleteConfirmDialog";
import type { HorseNoteRaceOption } from "@/features/horseNote/presentational/HorseNoteModal/types";
import HorseNotesList from "@/features/horseNote/presentational/HorseNotesList";
import type { HorseNoteListItem } from "@/features/horseNote/presentational/HorseNotesList/types";
import {
	HorseNoteRequestError,
	deleteNote,
} from "@/features/horseNote/requests/horseNotes";
import type { HorseNote } from "@/features/horseNote/types/horseNote";
import { formatDateDisplay } from "@/utils/date";
import { useMemo, useState } from "react";
import { toast } from "sonner";

type Props = {
	horseId: number;
	horseName: string;
	initialNotes: HorseNoteListItem[];
	raceOptions: HorseNoteRaceOption[];
};

type EditTarget = {
	id: number;
	content: string;
	raceContext: HorseNoteModalRaceContext;
};

type DeleteTarget = {
	id: number;
	contentPreview: string;
};

/**
 * 競走馬詳細画面で表示するメモ一覧のコンテナ。
 * 「メモを追加」「編集」操作で HorseNoteModalContainer を開き、
 * API 成功時に内部の notes state を更新する。
 */
const HorseNotesListContainer = ({
	horseId,
	horseName,
	initialNotes,
	raceOptions,
}: Props) => {
	const [notes, setNotes] = useState<HorseNoteListItem[]>(initialNotes);
	const [open, setOpen] = useState<boolean>(false);
	const [mode, setMode] = useState<"create" | "edit">("create");
	const [editTarget, setEditTarget] = useState<EditTarget | null>(null);
	const [deleteOpen, setDeleteOpen] = useState<boolean>(false);
	const [deleteSubmitting, setDeleteSubmitting] = useState<boolean>(false);
	const [deleteErrorMessage, setDeleteErrorMessage] = useState<string | null>(
		null,
	);
	const [deleteTarget, setDeleteTarget] = useState<DeleteTarget | null>(null);

	const createRaceContext = useMemo<HorseNoteModalRaceContext>(() => {
		if (raceOptions.length === 0) {
			return { type: "none" };
		}
		return {
			type: "selectable",
			options: raceOptions,
			defaultUid: null,
		};
	}, [raceOptions]);

	const handleAddClick = () => {
		setMode("create");
		setEditTarget(null);
		setOpen(true);
	};

	const handleEditClick = (noteId: number) => {
		const target = notes.find((n) => n.id === noteId);
		if (target == null) {
			return;
		}
		const raceContext: HorseNoteModalRaceContext =
			target.race != null
				? { type: "fixed", label: target.race.label }
				: { type: "none" };
		setMode("edit");
		setEditTarget({
			id: target.id,
			content: target.content,
			raceContext,
		});
		setOpen(true);
	};

	const handleClose = () => {
		setOpen(false);
	};

	const handleDeleteClick = (noteId: number) => {
		const target = notes.find((n) => n.id === noteId);
		if (target == null) {
			return;
		}
		setDeleteTarget({
			id: target.id,
			contentPreview: target.content,
		});
		setDeleteErrorMessage(null);
		setDeleteOpen(true);
	};

	const handleDeleteOpenChange = (next: boolean) => {
		if (next) {
			return;
		}
		if (deleteSubmitting) {
			return;
		}
		setDeleteOpen(false);
		setDeleteErrorMessage(null);
	};

	const handleDeleteConfirm = async () => {
		if (deleteTarget == null) {
			return;
		}
		setDeleteSubmitting(true);
		setDeleteErrorMessage(null);
		try {
			await deleteNote(deleteTarget.id);
			setNotes((current) => current.filter((n) => n.id !== deleteTarget.id));
			setDeleteOpen(false);
			setDeleteTarget(null);
		} catch (e) {
			const message =
				e instanceof HorseNoteRequestError
					? e.message
					: "メモの削除に失敗しました";
			setDeleteErrorMessage(message);
			toast.error(message);
		} finally {
			setDeleteSubmitting(false);
		}
	};

	const handleSuccess = (note: HorseNote) => {
		const item: HorseNoteListItem = {
			id: note.id,
			content: note.content,
			race:
				note.race != null
					? {
							uid: note.race.uid,
							label: buildRaceLabel(note.race),
						}
					: null,
			created_at: note.created_at,
			updated_at: note.updated_at,
		};
		setNotes((current) => {
			const existsIndex = current.findIndex((n) => n.id === item.id);
			if (existsIndex >= 0) {
				const next = [...current];
				next[existsIndex] = item;
				return next;
			}
			return [item, ...current];
		});
	};

	const modalRaceContext: HorseNoteModalRaceContext =
		mode === "edit" && editTarget != null
			? editTarget.raceContext
			: createRaceContext;

	return (
		<>
			<HorseNotesList
				notes={notes}
				onAddClick={handleAddClick}
				onEditClick={handleEditClick}
				onDeleteClick={handleDeleteClick}
			/>
			<HorseNoteModalContainer
				open={open}
				mode={mode}
				horseId={horseId}
				horseName={horseName}
				noteId={
					mode === "edit" && editTarget != null ? editTarget.id : undefined
				}
				initialContent={
					mode === "edit" && editTarget != null ? editTarget.content : ""
				}
				raceId={null}
				raceContext={modalRaceContext}
				onClose={handleClose}
				onSuccess={handleSuccess}
			/>
			<HorseNoteDeleteConfirmDialog
				open={deleteOpen}
				noteContentPreview={deleteTarget?.contentPreview ?? ""}
				submitting={deleteSubmitting}
				errorMessage={deleteErrorMessage}
				onOpenChange={handleDeleteOpenChange}
				onConfirm={handleDeleteConfirm}
			/>
		</>
	);
};

const buildRaceLabel = (race: NonNullable<HorseNote["race"]>): string => {
	const base = `${formatDateDisplay(race.race_date)} ${race.venue_name} ${race.race_number}R`;
	return race.race_name != null ? `${base} ${race.race_name}` : base;
};

export default HorseNotesListContainer;
