export type RaceInfoSectionProps = {
	selectedVenue: string;
	selectedRaceDate: string;
	selectedRaceNumber: number;
	onVenueChange: (venue: string) => void;
	onRaceDateChange: (date: string) => void;
	onRaceNumberChange: (num: number) => void;
};
