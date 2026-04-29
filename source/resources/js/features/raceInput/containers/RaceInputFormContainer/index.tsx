import RaceInputForm from "@/features/raceInput/presentational/RaceInputForm";
import { useFormSubmit } from "@/hooks/useFormSubmit";
import { useRef } from "react";
import { toast } from "sonner";

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
	// フォームから受け取る送信成功時のリセット関数を保持する。
	// useFormSubmit の onSuccess は最新のクロージャを参照する必要があるため ref で受け渡す。
	const formOnSuccessRef = useRef<(() => void) | undefined>(undefined);

	const { handleSubmit: submit } = useFormSubmit<RaceInputFormData>({
		url: "/races",
		onSuccess: () => {
			toast.success("レース情報を登録しました");
			formOnSuccessRef.current?.();
		},
		onError: (errors) => {
			for (const message of Object.values(errors)) {
				toast.error(message);
			}
		},
	});

	const handleSubmit = (data: RaceInputFormData, onSuccess: () => void) => {
		formOnSuccessRef.current = onSuccess;
		submit(data);
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
