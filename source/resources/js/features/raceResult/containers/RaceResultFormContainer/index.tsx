import RaceResultForm from "@/features/raceResult/presentational/RaceResultForm";
import { useFormSubmit } from "@/hooks/useFormSubmit";
import { useState } from "react";

type RaceResultFormContainerProps = {
	raceUid: string;
	venueName: string;
	raceDate: string;
	raceNumber: number;
	disabled?: boolean;
};

type RaceResultFormData = {
	result_text: string;
	text: string;
};

const RaceResultFormContainer = ({
	raceUid,
	venueName,
	raceDate,
	raceNumber,
	disabled,
}: RaceResultFormContainerProps) => {
	const [resultPasteValue, setResultPasteValue] = useState("");
	const [payoutPasteValue, setPayoutPasteValue] = useState("");
	const [resultParseError, setResultParseError] = useState<string | null>(null);
	const [payoutParseError, setPayoutParseError] = useState<string | null>(null);

	const { isSubmitting, handleSubmit: submit } =
		useFormSubmit<RaceResultFormData>({
			url: `/races/${raceUid}/result`,
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
			},
		});

	const handleSubmit = () => {
		setResultParseError(null);
		setPayoutParseError(null);
		submit({ result_text: resultPasteValue, text: payoutPasteValue });
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
			disabled={disabled}
		/>
	);
};

export default RaceResultFormContainer;
