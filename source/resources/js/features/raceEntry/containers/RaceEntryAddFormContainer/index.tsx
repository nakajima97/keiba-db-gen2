import { useState } from "react";
import { toast } from "sonner";
import RaceEntryEditForm from "@/features/raceEntry/presentational/RaceEntryEditForm";
import type {
	RaceEntryEditFormErrors,
	RaceEntryEditFormValues,
	RaceInfo,
} from "@/features/raceEntry/presentational/RaceEntryEditForm/types";
import { useFormSubmit } from "@/hooks/useFormSubmit";

export type RaceEntryAddFormContainerProps = {
	raceUid: string;
	raceInfo: RaceInfo;
};

const initialValues: RaceEntryEditFormValues = {
	horse_name: "",
	jockey_name: "",
	frame_number: 0,
	horse_number: 0,
	weight: "",
	horse_weight: "",
};

const RaceEntryAddFormContainer = ({
	raceUid,
	raceInfo,
}: RaceEntryAddFormContainerProps) => {
	const [values, setValues] = useState<RaceEntryEditFormValues>(initialValues);
	const [errors, setErrors] = useState<RaceEntryEditFormErrors>({});

	const { isSubmitting, handleSubmit: submit } =
		useFormSubmit<RaceEntryEditFormValues>({
			url: `/races/${raceUid}/entries/add`,
			method: "post",
			onSuccess: () => {
				toast.success("出走馬を追加しました");
				setErrors({});
			},
			onError: (validationErrors) => {
				setErrors(validationErrors as RaceEntryEditFormErrors);
				for (const message of Object.values(validationErrors)) {
					toast.error(message);
				}
			},
		});

	const handleChange = (
		field: keyof RaceEntryEditFormValues,
		value: string,
	) => {
		setValues((prev) => {
			if (field === "frame_number" || field === "horse_number") {
				return { ...prev, [field]: value === "" ? 0 : Number(value) };
			}
			return { ...prev, [field]: value };
		});
	};

	const handleSubmit = () => {
		const weight =
			values.weight === "" || values.weight.includes(".")
				? values.weight
				: `${values.weight}.0`;
		submit({ ...values, weight });
	};

	return (
		<RaceEntryEditForm
			raceUid={raceUid}
			raceInfo={raceInfo}
			values={values}
			errors={errors}
			isSubmitting={isSubmitting}
			onChange={handleChange}
			onSubmit={handleSubmit}
			headingLabel="出走馬個別追加"
			submitLabel="追加"
		/>
	);
};

export default RaceEntryAddFormContainer;
