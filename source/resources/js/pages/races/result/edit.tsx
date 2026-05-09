import RaceResultDetailContainer from "@/features/raceResult/containers/RaceResultDetailContainer";
import type { RaceMyTicket } from "@/features/raceResult/presentational/RaceMyTicketSection";
import type { RaceResultDetailProps } from "@/features/raceResult/presentational/RaceResultDetail";
import { Head, usePage } from "@inertiajs/react";

type RaceResultEditProps = {
	race: RaceResultDetailProps["race"] & {
		id?: number;
		race_name?: string | null;
	};
	tickets?: RaceMyTicket[];
};

const RaceResultEdit = () => {
	const { race, tickets } = usePage<RaceResultEditProps>().props;

	return (
		<>
			<Head title="レース結果" />
			<RaceResultDetailContainer race={race} tickets={tickets ?? []} />
		</>
	);
};

export default RaceResultEdit;
