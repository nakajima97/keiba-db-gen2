import { useForm } from "@inertiajs/react";
import { toast } from "sonner";
import RaceEntryEditForm from "@/features/raceEntry/presentational/RaceEntryEditForm";
import type {
	RaceEntryEditFormValues,
	RaceInfo,
} from "@/features/raceEntry/presentational/RaceEntryEditForm/types";
import { update as raceEntryUpdate } from "@/routes/races/entries";

export type RaceEntryEditFormContainerProps = {
	raceUid: string;
	entryId: number;
	raceInfo: RaceInfo;
	initialValues: RaceEntryEditFormValues;
};

const RaceEntryEditFormContainer = ({
	raceUid,
	entryId,
	raceInfo,
	initialValues,
}: RaceEntryEditFormContainerProps) => {
	const form = useForm<RaceEntryEditFormValues>(initialValues);

	const handleChange = (
		field: keyof RaceEntryEditFormValues,
		value: string,
	) => {
		if (field === "frame_number" || field === "horse_number") {
			form.setData(field, value === "" ? "" : Number(value));
		} else {
			form.setData(field, value);
		}
	};

	const handleSubmit = () => {
		form.put(raceEntryUpdate.url({ race: raceUid, entry: entryId }), {
			onSuccess: () => {
				toast.success("出走馬を更新しました");
			},
		});
	};

	return (
		<RaceEntryEditForm
			raceUid={raceUid}
			raceInfo={raceInfo}
			values={form.data}
			errors={form.errors}
			isSubmitting={form.processing}
			onChange={handleChange}
			onSubmit={handleSubmit}
		/>
	);
};

export default RaceEntryEditFormContainer;
