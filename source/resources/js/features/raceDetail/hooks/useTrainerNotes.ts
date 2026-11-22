import { useMemo, useState } from "react";
import type { RaceEntry } from "@/features/raceDetail/presentational/RaceDetail/types";

type TrainerNoteModalState = {
	open: boolean;
	trainerId: number | null;
};

type UseTrainerNotesReturn = {
	trainerNoteModal: TrainerNoteModalState;
	trainerNoteTargetEntry: RaceEntry | undefined;
	handleTrainerNoteClick: (trainerId: number) => void;
	handleTrainerNoteClose: () => void;
	handleTrainerNoteSaved: (params: {
		trainerId: number;
		id: number;
		content: string;
	}) => void;
	handleTrainerNoteDeleted: (params: { trainerId: number }) => void;
};

/**
 * レース詳細画面の調教師メモモーダルの開閉 state と、保存・削除後の出走馬 state 同期を管理する。
 * 同じ調教師の管理馬が複数出走している場合、保存・削除はその調教師の全出走馬に反映する。
 * API 呼び出しは TrainerNoteModalContainer 側が担う。
 */
export const useTrainerNotes = (
	entries: RaceEntry[],
	setEntries: (updater: (current: RaceEntry[]) => RaceEntry[]) => void,
): UseTrainerNotesReturn => {
	const [trainerNoteModal, setTrainerNoteModal] =
		useState<TrainerNoteModalState>({
			open: false,
			trainerId: null,
		});

	const handleTrainerNoteClick = (trainerId: number) => {
		setTrainerNoteModal({ open: true, trainerId });
	};

	const handleTrainerNoteClose = () => {
		setTrainerNoteModal((current) => ({ ...current, open: false }));
	};

	const handleTrainerNoteSaved = (params: {
		trainerId: number;
		id: number;
		content: string;
	}) => {
		setEntries((current) =>
			current.map((entry) =>
				entry.trainer_id === params.trainerId
					? {
							...entry,
							trainer_note: { id: params.id, content: params.content },
						}
					: entry,
			),
		);
	};

	const handleTrainerNoteDeleted = (params: { trainerId: number }) => {
		setEntries((current) =>
			current.map((entry) =>
				entry.trainer_id === params.trainerId
					? { ...entry, trainer_note: null }
					: entry,
			),
		);
	};

	const trainerNoteTargetEntry = useMemo(
		() =>
			trainerNoteModal.trainerId != null
				? entries.find((e) => e.trainer_id === trainerNoteModal.trainerId)
				: undefined,
		[entries, trainerNoteModal.trainerId],
	);

	return {
		trainerNoteModal,
		trainerNoteTargetEntry,
		handleTrainerNoteClick,
		handleTrainerNoteClose,
		handleTrainerNoteSaved,
		handleTrainerNoteDeleted,
	};
};
