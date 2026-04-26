export type RaceHistoryItem = {
	race_uid: string;
	race_date: string;
	venue_name: string;
	race_number: number;
	race_name: string | null;
	finishing_order: number;
	jockey_name: string;
	popularity: number;
};

export type HorseDetailItem = {
	id: number;
	name: string;
	birth_year: number;
	race_histories: RaceHistoryItem[];
};

export type HorseDetailProps = {
	horse: HorseDetailItem;
};
