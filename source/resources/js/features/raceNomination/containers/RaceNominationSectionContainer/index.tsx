import { useState } from "react";
import { useHorseCandidates } from "../../hooks/useHorseCandidates";
import RaceNominationAddForm from "../../presentational/RaceNominationAddForm";
import RaceNominationDeleteConfirmDialog from "../../presentational/RaceNominationDeleteConfirmDialog";
import RaceNominationSection from "../../presentational/RaceNominationSection";
import type { RaceNominationSectionProps } from "../../presentational/RaceNominationSection/types";
import type {
	HorseCandidate,
	RaceNomination,
	RaceNominationStatus,
} from "../../types/raceNomination";

type AddResult = { ok: true } | { ok: false; message: string };

type MarkProps = Pick<
	RaceNominationSectionProps,
	| "markColumns"
	| "marks"
	| "markMemos"
	| "onAddOtherColumn"
	| "onChangeColumnLabel"
	| "onRemoveOtherColumn"
	| "onMarkChange"
	| "onMarkMemoClick"
>;

type Props = MarkProps & {
	nominations: ReadonlyArray<RaceNomination>;
	onAdd: (input: { horseId: number } | { horseName: string }) => Promise<AddResult>;
	onRemove: (nominationId: number) => Promise<boolean>;
	onStatusChange: (
		nominationId: number,
		status: RaceNominationStatus | null,
	) => void;
	onNoteClick: (horseId: number) => void;
};

const RaceNominationSectionContainer = ({
	nominations,
	onAdd,
	onRemove,
	onStatusChange,
	onNoteClick,
	...markProps
}: Props) => {
	const [horseName, setHorseName] = useState("");
	const [submitting, setSubmitting] = useState(false);
	const [errorMessage, setErrorMessage] = useState<string | null>(null);
	const [deleteTargetId, setDeleteTargetId] = useState<number | null>(null);
	const [deleting, setDeleting] = useState(false);
	const candidates = useHorseCandidates(horseName);

	const deleteTarget = nominations.find(
		(nomination) => nomination.id === deleteTargetId,
	);

	const add = async (input: { horseId: number } | { horseName: string }) => {
		setSubmitting(true);
		setErrorMessage(null);
		const result = await onAdd(input);
		setSubmitting(false);
		if (result.ok) {
			setHorseName("");
		} else {
			setErrorMessage(result.message);
		}
	};

	const handleValueChange = (value: string) => {
		setHorseName(value);
		setErrorMessage(null);
	};

	const handleCandidateSelect = (candidate: HorseCandidate) => {
		void add({ horseId: candidate.id });
	};

	const handleSubmit = () => {
		void add({ horseName: horseName.trim() });
	};

	const handleDeleteConfirm = async () => {
		if (deleteTargetId === null) {
			return;
		}
		setDeleting(true);
		const succeeded = await onRemove(deleteTargetId);
		setDeleting(false);
		if (succeeded) {
			setDeleteTargetId(null);
		}
	};

	return (
		<>
			<RaceNominationSection
				{...markProps}
				nominations={nominations}
				addForm={
					<RaceNominationAddForm
						value={horseName}
						candidates={candidates}
						submitting={submitting}
						errorMessage={errorMessage}
						onValueChange={handleValueChange}
						onCandidateSelect={handleCandidateSelect}
						onSubmit={handleSubmit}
					/>
				}
				onStatusChange={onStatusChange}
				onNoteClick={onNoteClick}
				onDeleteClick={setDeleteTargetId}
			/>
			<RaceNominationDeleteConfirmDialog
				open={deleteTarget != null}
				horseName={deleteTarget?.horse_name ?? ""}
				submitting={deleting}
				onOpenChange={(open) => {
					if (!open && !deleting) {
						setDeleteTargetId(null);
					}
				}}
				onConfirm={handleDeleteConfirm}
			/>
		</>
	);
};

export default RaceNominationSectionContainer;
