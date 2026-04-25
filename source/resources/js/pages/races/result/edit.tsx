import { Head, usePage } from "@inertiajs/react";
import RaceResultDetail from "@/features/raceResult/presentational/RaceResultDetail";
import type { RaceResultDetailProps } from "@/features/raceResult/presentational/RaceResultDetail";

type RaceResultEditProps = {
	race: RaceResultDetailProps["race"];
};

export default function RaceResultEdit() {
	const { race } = usePage<RaceResultEditProps>().props;

	return (
		<>
			<Head title="レース結果" />
			<RaceResultDetail race={race} />
		</>
	);
}
