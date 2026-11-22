import type { HorseCandidate } from "../../types/raceNomination";

export type RaceNominationAddFormProps = {
	value: string;
	candidates: ReadonlyArray<HorseCandidate>;
	submitting: boolean;
	errorMessage: string | null;
	onValueChange: (value: string) => void;
	onCandidateSelect: (candidate: HorseCandidate) => void;
	onSubmit: () => void;
};
