import { router } from "@inertiajs/react";
import { useState } from "react";
import HorseNoteModalContainer from "@/features/horseNote/containers/HorseNoteModalContainer";
import type { HorseNote } from "@/features/horseNote/types/horseNote";
import DeleteResultModal from "@/features/raceResult/components/DeleteResultModal";
import RaceResultDetail from "@/features/raceResult/presentational/RaceResultDetail";
import type {
	FinishingHorse,
	RaceResultDetailProps,
} from "@/features/raceResult/presentational/RaceResultDetail/types";
import { deleteRaceResult } from "@/features/raceResult/requests/raceResults";
import { formatDateDisplay } from "@/utils/date";

type RaceProps = RaceResultDetailProps["race"] & {
	id?: number;
	race_name?: string | null;
};

type Props = {
	race: RaceProps;
};

const buildRaceLabel = (race: RaceProps): string => {
	const base = `${formatDateDisplay(race.race_date)} ${race.venue_name} ${race.race_number}R`;
	return race.race_name != null ? `${base} ${race.race_name}` : base;
};

/**
 * レース結果画面のコンテナ。
 * メモセルのクリックでモーダルを開き、API 成功時に finishing_horses の note を更新する。
 * 「レース結果を削除」ボタンで削除確認モーダルを表示し、確定時に DELETE API を呼んで結果入力画面に遷移する。
 */
const RaceResultDetailContainer = ({ race }: Props) => {
	const [finishingHorses, setFinishingHorses] = useState<FinishingHorse[]>(
		race.finishing_horses,
	);
	const [noteModal, setNoteModal] = useState<{
		open: boolean;
		horseId: number | null;
		horseName: string;
	}>({ open: false, horseId: null, horseName: "" });
	const [deleteModal, setDeleteModal] = useState<{
		open: boolean;
		isLoading: boolean;
		errorMessage: string | null;
	}>({ open: false, isLoading: false, errorMessage: null });

	const handleNoteClick = (horseId: number) => {
		const target = finishingHorses.find((h) => h.horse_id === horseId);
		if (target == null) {
			return;
		}
		setNoteModal({
			open: true,
			horseId,
			horseName: target.horse_name,
		});
	};

	const handleNoteClose = () => {
		setNoteModal((current) => ({ ...current, open: false }));
	};

	const handleNoteSuccess = (note: HorseNote) => {
		setFinishingHorses((current) =>
			current.map((horse) => {
				if (horse.horse_id !== note.horse_id) {
					return horse;
				}
				return {
					...horse,
					note: {
						id: note.id,
						content: note.content,
						source: note.race_id != null ? "race" : "horse",
					},
				};
			}),
		);
	};

	const handleDeleteClick = () => {
		setDeleteModal({ open: true, isLoading: false, errorMessage: null });
	};

	const handleDeleteCancel = () => {
		setDeleteModal({ open: false, isLoading: false, errorMessage: null });
	};

	const handleDeleteConfirm = async () => {
		setDeleteModal((current) => ({
			...current,
			isLoading: true,
			errorMessage: null,
		}));
		try {
			await deleteRaceResult(race.uid);
			router.visit(`/races/${race.uid}/result/new`);
		} catch (error) {
			const message =
				error instanceof Error
					? error.message
					: "レース結果の削除に失敗しました。時間をおいて再度お試しください。";
			setDeleteModal({
				open: true,
				isLoading: false,
				errorMessage: message,
			});
		}
	};

	const localRace = { ...race, finishing_horses: finishingHorses };
	const selectedHorse =
		noteModal.horseId != null
			? finishingHorses.find((h) => h.horse_id === noteModal.horseId)
			: undefined;
	const selectedNote = selectedHorse?.note ?? null;

	return (
		<>
			<RaceResultDetail
				race={localRace}
				onNoteClick={handleNoteClick}
				onDeleteClick={handleDeleteClick}
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
			<DeleteResultModal
				open={deleteModal.open}
				isLoading={deleteModal.isLoading}
				errorMessage={deleteModal.errorMessage}
				onConfirm={handleDeleteConfirm}
				onCancel={handleDeleteCancel}
			/>
		</>
	);
};

export default RaceResultDetailContainer;
