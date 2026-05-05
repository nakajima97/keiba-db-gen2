import { useForm } from "@inertiajs/react";
import { toast } from "sonner";
import RaceInputForm from "@/features/raceInput/presentational/RaceInputForm";

export type RaceInputFormContainerProps = {
	venues: { id: number; name: string }[];
	initialVenueId?: number;
	initialRaceDate?: string;
	initialRaceNumber?: number;
	initialRaceName?: string;
};

type RaceInputFormData = {
	venue_id: number;
	race_date: string;
	race_number: number;
	race_name: string | undefined;
	paste_text: string;
};

const RaceInputFormContainer = ({
	venues,
	initialVenueId,
	initialRaceDate,
	initialRaceNumber,
	initialRaceName,
}: RaceInputFormContainerProps) => {
	const form = useForm<RaceInputFormData>({
		venue_id: 0,
		race_date: "",
		race_number: 0,
		race_name: undefined,
		paste_text: "",
	});

	const handleSubmit = (data: RaceInputFormData, onSuccess: () => void) => {
		form.transform(() => data);
		form.post("/races", {
			onSuccess: () => {
				toast.success("レース情報を登録しました");
				onSuccess();
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
			initialRaceName={initialRaceName}
			onSubmit={handleSubmit}
		/>
	);
};

export default RaceInputFormContainer;
