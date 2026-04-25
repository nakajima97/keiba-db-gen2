import { useState } from "react";
import { toast } from "sonner";
import RaceEntryRegistrationForm from "@/features/raceEntry/presentational/RaceEntryRegistrationForm";
import type { RaceInfo } from "@/features/raceEntry/presentational/RaceEntryRegistrationForm/types";
import { useFormSubmit } from "@/hooks/useFormSubmit";

export type RaceEntryRegistrationFormContainerProps = {
	raceUid: string;
	raceInfo: RaceInfo;
};

type RaceEntryFormData = {
	paste_text: string;
};

export default function RaceEntryRegistrationFormContainer({
	raceUid,
	raceInfo,
}: RaceEntryRegistrationFormContainerProps) {
	const [pastedText, setPastedText] = useState("");

	const { isSubmitting, handleSubmit: submit } = useFormSubmit<RaceEntryFormData>({
		url: `/races/${raceUid}/entries`,
		onSuccess: () => {
			toast.success("出走馬を登録しました");
			setPastedText("");
		},
		onError: (errors) => {
			for (const message of Object.values(errors)) {
				toast.error(message);
			}
		},
	});

	const handleSubmit = () => {
		submit({ paste_text: pastedText });
	};

	return (
		<RaceEntryRegistrationForm
			raceInfo={raceInfo}
			pastedText={pastedText}
			isSubmitting={isSubmitting}
			onPastedTextChange={setPastedText}
			onSubmit={handleSubmit}
		/>
	);
}
