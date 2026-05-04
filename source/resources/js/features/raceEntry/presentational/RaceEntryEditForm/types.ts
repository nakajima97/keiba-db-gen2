export type RaceInfo = {
	race_date: string;
	venue_name: string;
	race_number: number;
};

export type RaceEntryEditFormValues = {
	horse_name: string;
	jockey_name: string;
	frame_number: number;
	horse_number: number;
	weight: string;
	horse_weight: string;
};

export type RaceEntryEditFormErrors = Partial<
	Record<keyof RaceEntryEditFormValues, string>
>;

export type RaceEntryEditFormProps = {
	raceUid: string;
	raceInfo: RaceInfo;
	values: RaceEntryEditFormValues;
	errors: RaceEntryEditFormErrors;
	isSubmitting: boolean;
	onChange: (field: keyof RaceEntryEditFormValues, value: string) => void;
	onSubmit: () => void;
	headingLabel?: string;
	submitLabel?: string;
};
