import { router } from "@inertiajs/react";
import { useState } from "react";
import RaceResultForm from "@/features/raceResult/presentational/RaceResultForm";

type RaceResultFormContainerProps = {
	raceUid: string;
	venueName: string;
	raceDate: string;
	raceNumber: number;
};

export default function RaceResultFormContainer({
	raceUid,
	venueName,
	raceDate,
	raceNumber,
}: RaceResultFormContainerProps) {
	const [resultPasteValue, setResultPasteValue] = useState("");
	const [payoutPasteValue, setPayoutPasteValue] = useState("");
	const [resultParseError, setResultParseError] = useState<string | null>(null);
	const [payoutParseError, setPayoutParseError] = useState<string | null>(null);
	const [isSubmitting, setIsSubmitting] = useState(false);

	const handleSubmit = () => {
		setIsSubmitting(true);
		setResultParseError(null);
		setPayoutParseError(null);

		router.post(
			`/races/${raceUid}/result`,
			{ result_text: resultPasteValue, text: payoutPasteValue },
			{
				onError: (errors) => {
					if (errors.result_text) {
						setResultParseError(errors.result_text);
					}
					if (errors.text) {
						setPayoutParseError(errors.text);
					}
					if (!errors.result_text && !errors.text) {
						setPayoutParseError("保存に失敗しました。");
					}
					setIsSubmitting(false);
				},
				onFinish: () => {
					setIsSubmitting(false);
				},
			},
		);
	};

	return (
		<RaceResultForm
			venueName={venueName}
			raceDate={raceDate}
			raceNumber={raceNumber}
			resultPasteValue={resultPasteValue}
			onResultPasteChange={setResultPasteValue}
			resultParseError={resultParseError}
			payoutPasteValue={payoutPasteValue}
			onPayoutPasteChange={setPayoutPasteValue}
			payoutParseError={payoutParseError}
			onSubmit={handleSubmit}
			isSubmitting={isSubmitting}
		/>
	);
}
