import HorseNoteModalContainer, {
	type HorseNoteModalRaceContext,
} from "@/features/horseNote/containers/HorseNoteModalContainer";
import type { HorseNoteCellSource } from "@/features/horseNote/presentational/HorseNoteCell/types";
import HorseNoteIconButton from "@/features/horseNote/presentational/HorseNoteIconButton";
import type { HorseNote } from "@/features/horseNote/types/horseNote";
import { useState } from "react";

type LocalNote = {
	id: number;
	content: string;
	source: HorseNoteCellSource;
};

type Props = {
	horseId: number;
	horseName: string;
	raceId: number;
	raceLabel: string;
	initialNote: { id: number; content: string; source: "race" | "horse" } | null;
};

/**
 * レース詳細画面の出馬表に配置するメモアイコンボタンのコンテナ。
 * クリックでモーダルを開く。
 */
const HorseNoteIconButtonContainer = ({
	horseId,
	horseName,
	raceId,
	raceLabel,
	initialNote,
}: Props) => {
	const [note, setNote] = useState<LocalNote | null>(
		initialNote != null
			? {
					id: initialNote.id,
					content: initialNote.content,
					source: initialNote.source,
				}
			: null,
	);
	const [open, setOpen] = useState<boolean>(false);

	const mode: "create" | "edit" = note != null ? "edit" : "create";

	const raceContext: HorseNoteModalRaceContext =
		note != null
			? note.source === "race"
				? { type: "fixed", label: raceLabel }
				: { type: "none" }
			: { type: "fixed", label: raceLabel };

	const handleClick = () => {
		setOpen(true);
	};

	const handleClose = () => {
		setOpen(false);
	};

	const handleSuccess = (saved: HorseNote) => {
		setNote({
			id: saved.id,
			content: saved.content,
			source: saved.race_id != null ? "race" : "horse",
		});
	};

	return (
		<>
			<HorseNoteIconButton
				hasNote={note != null}
				ariaLabel={`${horseName}のメモ`}
				onClick={handleClick}
			/>
			<HorseNoteModalContainer
				open={open}
				mode={mode}
				horseId={horseId}
				horseName={horseName}
				noteId={note?.id}
				initialContent={note?.content ?? ""}
				raceId={mode === "create" ? raceId : null}
				raceContext={raceContext}
				onClose={handleClose}
				onSuccess={handleSuccess}
			/>
		</>
	);
};

export default HorseNoteIconButtonContainer;
