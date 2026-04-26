export type RaceEntry = {
	frame_number: number;
	horse_number: number;
	horse_id: number;
	horse_name: string;
	jockey_name: string;
	weight: number | null;
};

export type RaceDetailItem = {
	uid: string;
	race_date: string;
	venue_name: string;
	race_number: number;
	entries: RaceEntry[];
};

export type RaceDetailProps = {
	race: RaceDetailItem;
};
