import { Head, router, usePage } from "@inertiajs/react";
import { useState } from "react";
import RaceList from "@/features/raceList/presentational/RaceList";
import type {
	RaceListItem,
	Venue,
} from "@/features/raceList/presentational/RaceList/types";
import { index as racesIndex } from "@/routes/races";

type RacesIndexProps = {
	races: RaceListItem[];
	venues: Venue[];
	filters: {
		race_date: string | null;
		venue_id: number | null;
	};
};

export default function RacesIndex() {
	const { races, venues, filters } = usePage<RacesIndexProps>().props;

	const [selectedDate, setSelectedDate] = useState<string>(
		filters.race_date ?? "",
	);
	const [selectedVenueId, setSelectedVenueId] = useState<string>(
		filters.venue_id != null ? String(filters.venue_id) : "all",
	);

	const buildQuery = (date: string, venueId: string) => {
		const query: Record<string, string> = {};
		if (date !== "") {
			query.race_date = date;
		}
		if (venueId !== "all") {
			query.venue_id = venueId;
		}
		return query;
	};

	const handleDateChange = (date: string) => {
		setSelectedDate(date);
		router.get(racesIndex.url(), buildQuery(date, selectedVenueId), {
			preserveState: true,
			replace: true,
		});
	};

	const handleVenueChange = (venueId: string) => {
		setSelectedVenueId(venueId);
		if (selectedDate !== "") {
			router.get(racesIndex.url(), buildQuery(selectedDate, venueId), {
				preserveState: true,
				replace: true,
			});
		}
	};

	return (
		<>
			<Head title="レース一覧" />
			<RaceList
				races={races}
				venues={venues}
				selectedDate={selectedDate}
				selectedVenueId={selectedVenueId}
				onDateChange={handleDateChange}
				onVenueChange={handleVenueChange}
			/>
		</>
	);
}
