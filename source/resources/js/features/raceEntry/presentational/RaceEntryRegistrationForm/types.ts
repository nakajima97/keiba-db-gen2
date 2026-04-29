export type RaceInfo = {
	race_date: string;
	venue_name: string;
	race_number: number;
};

export type RaceEntryRegistrationFormProps = {
	raceUid: string;
	raceInfo: RaceInfo;
	pastedText: string;
	isSubmitting: boolean;
	onPastedTextChange: (text: string) => void;
	onSubmit: () => void;
};
