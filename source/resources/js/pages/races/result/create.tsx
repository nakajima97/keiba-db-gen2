import { Head, usePage } from "@inertiajs/react";
import RaceResultFormContainer from "@/features/raceResult/containers/RaceResultFormContainer";

type RaceResultCreateProps = {
	race: {
		uid: string;
		venue_name: string;
		race_date: string;
		race_number: number;
		has_existing_result: boolean;
	};
};

export default function RaceResultCreate() {
	const { race } = usePage<RaceResultCreateProps>().props;

	return (
		<>
			<Head title="レース結果入力" />
			<RaceResultFormContainer
				raceUid={race.uid}
				venueName={race.venue_name}
				raceDate={race.race_date}
				raceNumber={race.race_number}
				disabled={race.has_existing_result}
			/>
		</>
	);
}
