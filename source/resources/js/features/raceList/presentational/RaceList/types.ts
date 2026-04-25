export type RaceListItem = {
	uid: string;
	race_date: string;
	venue_name: string;
	race_number: number;
	race_name: string | null;
};

export type Venue = {
	id: number;
	name: string;
};

export type RaceListProps = {
	races: RaceListItem[];
	venues: Venue[];
	selectedDate: string;
	selectedVenueId: string;
	onDateChange: (date: string) => void;
	onVenueChange: (venueId: string) => void;
};
