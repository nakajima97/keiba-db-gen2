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

export type FinishingHorse = {
	finishing_order: number;
	frame_number: number;
	horse_number: number;
	horse_id: number | null;
	horse_name: string;
	jockey_name: string;
	race_time: string;
};

export type RaceResultDetailProps = {
	race: {
		uid: string;
		venue_name: string;
		race_date: string;
		race_number: number;
		payouts: PayoutEntry[];
		finishing_horses: FinishingHorse[];
	};
};
