import { router } from "@inertiajs/react";
import { toast } from "sonner";
import RaceInputForm from "@/features/raceInput/presentational/RaceInputForm";

export type RaceInputFormContainerProps = {
	venues: { id: number; name: string }[];
	initialVenueId?: number;
	initialRaceDate?: string;
	initialRaceNumber?: number;
};

export default function RaceInputFormContainer({
	venues,
	initialVenueId,
	initialRaceDate,
	initialRaceNumber,
}: RaceInputFormContainerProps) {
	const handleSubmit = (data: {
		venue_id: number;
		race_date: string;
		race_number: number;
		paste_text: string;
	}) => {
		router.post("/races", data, {
			onSuccess: () => {
				toast.success("レース情報を登録しました");
			},
			onError: (errors) => {
				for (const message of Object.values(errors)) {
					toast.error(message);
				}
			},
		});
	};

	return (
		<RaceInputForm
			venues={venues}
			initialVenueId={initialVenueId}
			initialRaceDate={initialRaceDate}
			initialRaceNumber={initialRaceNumber}
			onSubmit={handleSubmit}
		/>
	);
}
