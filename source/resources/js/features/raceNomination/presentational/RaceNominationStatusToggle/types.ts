import type { RaceNominationStatus } from "../../types/raceNomination";

export type RaceNominationStatusToggleProps = {
	value: RaceNominationStatus | null;
	horseName: string;
	onChange: (value: RaceNominationStatus | null) => void;
};
