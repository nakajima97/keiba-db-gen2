import { Head, usePage } from "@inertiajs/react";
import RaceInputFormContainer from "@/features/raceInput/containers/RaceInputFormContainer";

type RacesNewProps = {
	venues: { id: number; name: string }[];
	last_venue_id?: number;
	last_race_date?: string;
	last_race_number?: number;
};

export default function RacesNew() {
	const { venues, last_venue_id, last_race_date, last_race_number } =
		usePage<RacesNewProps>().props;

	return (
		<>
			<Head title="レース情報入力" />
			<RaceInputFormContainer
				venues={venues}
				initialVenueId={last_venue_id}
				initialRaceDate={last_race_date}
				initialRaceNumber={last_race_number}
			/>
		</>
	);
}
