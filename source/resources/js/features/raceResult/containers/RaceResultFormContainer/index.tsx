import { useForm } from "@inertiajs/react";
import RaceResultForm from "@/features/raceResult/presentational/RaceResultForm";

type RaceResultFormContainerProps = {
	raceUid: string;
	venueName: string;
	raceDate: string;
	raceNumber: number;
	disabled?: boolean;
};

const RaceResultFormContainer = ({
	raceUid,
	venueName,
	raceDate,
	raceNumber,
	disabled,
}: RaceResultFormContainerProps) => {
	const form = useForm({ result_text: "", text: "" });

	const handleSubmit = () => {
		form.clearErrors();
		form.post(`/races/${raceUid}/result`, {
			onError: (errors) => {
				if (!errors.result_text && !errors.text) {
					form.setError("text", "保存に失敗しました。");
				}
			},
		});
	};

	return (
		<RaceResultForm
			venueName={venueName}
			raceDate={raceDate}
			raceNumber={raceNumber}
			resultPasteValue={form.data.result_text}
			onResultPasteChange={(value) => form.setData("result_text", value)}
			resultParseError={form.errors.result_text ?? null}
			payoutPasteValue={form.data.text}
			onPayoutPasteChange={(value) => form.setData("text", value)}
			payoutParseError={form.errors.text ?? null}
			onSubmit={handleSubmit}
			isSubmitting={form.processing}
			disabled={disabled}
		/>
	);
};

export default RaceResultFormContainer;
