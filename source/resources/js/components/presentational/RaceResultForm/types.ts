export type RaceResultFormProps = {
	venueName: string;
	raceDate: string;
	raceNumber: number;
	pasteValue: string;
	onPasteChange: (value: string) => void;
	parseError: string | null;
	onSubmit: () => void;
	isSubmitting: boolean;
};
