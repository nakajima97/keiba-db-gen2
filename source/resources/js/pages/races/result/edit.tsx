import RaceResultDetailContainer from "@/features/raceResult/containers/RaceResultDetailContainer";
import type { RaceResultDetailProps } from "@/features/raceResult/presentational/RaceResultDetail";
import { Head, usePage } from "@inertiajs/react";

type RaceResultEditProps = {
	race: RaceResultDetailProps["race"] & {
		id?: number;
		race_name?: string | null;
	};
};

const RaceResultEdit = () => {
	const { race } = usePage<RaceResultEditProps>().props;

	return (
		<>
			<Head title="レース結果" />
			<RaceResultDetailContainer race={race} />
		</>
	);
};

export default RaceResultEdit;
