import { Head, usePage } from "@inertiajs/react";
import HorseDetail from "@/features/horseDetail/presentational/HorseDetail";
import type { HorseDetailItem } from "@/features/horseDetail/presentational/HorseDetail/types";

type HorsesShowProps = {
	horse: HorseDetailItem;
};

export default function HorsesShow() {
	const { horse } = usePage<HorsesShowProps>().props;

	return (
		<>
			<Head title={horse.name} />
			<HorseDetail horse={horse} />
		</>
	);
}
