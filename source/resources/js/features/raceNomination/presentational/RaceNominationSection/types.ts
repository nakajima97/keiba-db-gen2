import type { ReactNode } from "react";
import type {
	RaceNomination,
	RaceNominationStatus,
} from "../../types/raceNomination";

export type RaceNominationSectionProps = {
	nominations: ReadonlyArray<RaceNomination>;
	addForm: ReactNode;
	onStatusChange: (
		nominationId: number,
		status: RaceNominationStatus | null,
	) => void;
	onNoteClick: (horseId: number) => void;
	onDeleteClick: (nominationId: number) => void;
};
