export type RaceResultFormProps = {
	venueName: string;
	raceDate: string;
	raceNumber: number;
	// 着順
	resultPasteValue: string;
	onResultPasteChange: (value: string) => void;
	resultParseError: string | null;
	// 払い戻し
	payoutPasteValue: string;
	onPayoutPasteChange: (value: string) => void;
	payoutParseError: string | null;
	// 共通
	onSubmit: () => void;
	isSubmitting: boolean;
	disabled?: boolean;
};
