import HorseNoteModalContainer, {
	type HorseNoteModalRaceContext,
} from "@/features/horseNote/containers/HorseNoteModalContainer";
import HorseNoteCell from "@/features/horseNote/presentational/HorseNoteCell";
import type { HorseNoteCellSource } from "@/features/horseNote/presentational/HorseNoteCell/types";
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
 * レース詳細・結果画面のテーブル内に配置するメモセル用コンテナ。
 * セルクリックでモーダルを開く。メモ未登録なら create、登録済なら edit モード。
 * race 紐づきのメモは race 固定、horse 紐づきは紐づけなしで開く。
 */
const HorseNoteCellContainer = ({
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
			<HorseNoteCell
				content={note?.content ?? null}
				source={note?.source ?? null}
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

export default HorseNoteCellContainer;
