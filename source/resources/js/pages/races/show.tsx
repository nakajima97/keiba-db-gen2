import { Head, usePage } from "@inertiajs/react";
import RaceDetailContainer from "@/features/raceDetail/containers/RaceDetailContainer";
import type { RaceDetailItem } from "@/features/raceDetail/presentational/RaceDetail/types";

type RacesShowProps = {
	race: RaceDetailItem & {
		id?: number;
		race_name?: string | null;
	};
	hasResult: boolean;
};

const RacesShow = () => {
	const { race, hasResult } = usePage<RacesShowProps>().props;

	return (
		<>
			<Head title="レース詳細" />
			<RaceDetailContainer race={race} hasResult={hasResult} />
		</>
	);
};

export default RacesShow;
