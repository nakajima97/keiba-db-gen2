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
	const [pasteValue, setPasteValue] = useState("");
	const [parseError, setParseError] = useState<string | null>(null);
	const [isSubmitting, setIsSubmitting] = useState(false);

	const handleSubmit = () => {
		setIsSubmitting(true);
		setParseError(null);

		router.post(
			`/races/${raceUid}/result`,
			{ text: pasteValue },
			{
				onError: (errors) => {
					setParseError(errors.text ?? "保存に失敗しました。");
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
			pasteValue={pasteValue}
			onPasteChange={setPasteValue}
			parseError={parseError}
			onSubmit={handleSubmit}
			isSubmitting={isSubmitting}
		/>
	);
}
