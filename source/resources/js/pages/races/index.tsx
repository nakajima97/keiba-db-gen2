import { Head, router, usePage } from "@inertiajs/react";
import { useEffect, useState } from "react";
import RaceList from "@/features/raceList/presentational/RaceList";
import type {
	RaceListItem,
	Venue,
} from "@/features/raceList/presentational/RaceList/types";
import { getDefaultRaceDate } from "@/features/raceList/utils/getDefaultRaceDate";
import { index as racesIndex } from "@/routes/races";

type RacesIndexProps = {
	races: RaceListItem[];
	venues: Venue[];
	filters: {
		race_date: string | null;
		venue_id: number | null;
	};
};

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

const RacesIndex = () => {
	const { races, venues, filters } = usePage<RacesIndexProps>().props;

	const [selectedDate, setSelectedDate] = useState<string>(
		filters.race_date ?? getDefaultRaceDate(),
	);
	const [selectedVenueId, setSelectedVenueId] = useState<string>(
		filters.venue_id != null ? String(filters.venue_id) : "all",
	);

	useEffect(() => {
		if (!filters.race_date) {
			router.get(
				racesIndex.url(),
				buildQuery(getDefaultRaceDate(), selectedVenueId),
				{ preserveState: true, replace: true },
			);
		}
	}, [filters.race_date, selectedVenueId]);

	useEffect(() => {
		if (
			selectedVenueId !== "all" &&
			!venues.some((v) => String(v.id) === selectedVenueId)
		) {
			setSelectedVenueId("all");
		}
	}, [venues, selectedVenueId]);

	const handleDateChange = (date: string) => {
		setSelectedDate(date);
		setSelectedVenueId("all");
		router.get(racesIndex.url(), buildQuery(date, "all"), {
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
};

export default RacesIndex;
