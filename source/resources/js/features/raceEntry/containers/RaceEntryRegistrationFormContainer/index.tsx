import { useForm } from "@inertiajs/react";
import { toast } from "sonner";
import RaceEntryRegistrationForm from "@/features/raceEntry/presentational/RaceEntryRegistrationForm";
import type { RaceInfo } from "@/features/raceEntry/presentational/RaceEntryRegistrationForm/types";

export type RaceEntryRegistrationFormContainerProps = {
	raceUid: string;
	raceInfo: RaceInfo;
};

const RaceEntryRegistrationFormContainer = ({
	raceUid,
	raceInfo,
}: RaceEntryRegistrationFormContainerProps) => {
	const form = useForm({ paste_text: "" });

	const handleSubmit = () => {
		form.post(`/races/${raceUid}/entries`, {
			onSuccess: () => {
				toast.success("出走馬を登録しました");
				form.reset("paste_text");
			},
			onError: (errors) => {
				for (const message of Object.values(errors)) {
					toast.error(message);
				}
			},
		});
	};

	return (
		<RaceEntryRegistrationForm
			raceUid={raceUid}
			raceInfo={raceInfo}
			pastedText={form.data.paste_text}
			isSubmitting={form.processing}
			onPastedTextChange={(value) => form.setData("paste_text", value)}
			onSubmit={handleSubmit}
		/>
	);
};

export default RaceEntryRegistrationFormContainer;
