export type PayoutEntry = {
	ticket_type_label: string;
	ticket_type_name: string;
	payout_amount: number;
	popularity: number;
	horses: Array<{
		horse_number: number;
		sort_order: number;
	}>;
};

export type RaceResultDetailProps = {
	race: {
		uid: string;
		venue_name: string;
		race_date: string;
		race_number: number;
		payouts: PayoutEntry[];
	};
};
