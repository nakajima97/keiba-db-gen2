import { Head, usePage } from "@inertiajs/react";
import RaceDetail from "@/features/raceDetail/presentational/RaceDetail";
import type { RaceDetailItem } from "@/features/raceDetail/presentational/RaceDetail/types";

type RacesShowProps = {
	race: RaceDetailItem;
};

const noop = () => {};

export default function RacesShow() {
	const { race } = usePage<RacesShowProps>().props;

	return (
		<>
			<Head title="レース詳細" />
			<RaceDetail
				race={race}
				onMarkChange={noop}
				onAddOtherColumn={noop}
				onRemoveOtherColumn={noop}
				onChangeColumnLabel={noop}
			/>
		</>
	);
}
