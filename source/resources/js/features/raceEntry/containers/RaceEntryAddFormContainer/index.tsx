import { useForm } from "@inertiajs/react";
import { toast } from "sonner";
import RaceEntryEditForm from "@/features/raceEntry/presentational/RaceEntryEditForm";
import type {
	RaceEntryEditFormValues,
	RaceInfo,
} from "@/features/raceEntry/presentational/RaceEntryEditForm/types";
import { store as raceEntryAddStore } from "@/routes/races/entries/add";

export type RaceEntryAddFormContainerProps = {
	raceUid: string;
	raceInfo: RaceInfo;
};

const initialValues: RaceEntryEditFormValues = {
	horse_name: "",
	jockey_name: "",
	frame_number: "",
	horse_number: "",
	weight: "",
	horse_weight: "",
};

const RaceEntryAddFormContainer = ({
	raceUid,
	raceInfo,
}: RaceEntryAddFormContainerProps) => {
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
		form.transform((data) => ({
			...data,
			weight:
				data.weight === "" || data.weight.includes(".")
					? data.weight
					: `${data.weight}.0`,
		}));
		form.post(raceEntryAddStore.url({ race: raceUid }), {
			onSuccess: () => {
				toast.success("出走馬を追加しました");
				form.clearErrors();
				form.reset();
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
			headingLabel="出走馬個別追加"
			submitLabel="追加"
		/>
	);
};

export default RaceEntryAddFormContainer;
