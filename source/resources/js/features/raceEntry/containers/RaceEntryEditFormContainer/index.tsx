import { useState } from "react";
import { toast } from "sonner";
import RaceEntryEditForm from "@/features/raceEntry/presentational/RaceEntryEditForm";
import type {
	RaceEntryEditFormErrors,
	RaceEntryEditFormValues,
	RaceInfo,
} from "@/features/raceEntry/presentational/RaceEntryEditForm/types";
import { useFormSubmit } from "@/hooks/useFormSubmit";

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
	const [values, setValues] = useState<RaceEntryEditFormValues>(initialValues);
	const [errors, setErrors] = useState<RaceEntryEditFormErrors>({});

	const { isSubmitting, handleSubmit: submit } =
		useFormSubmit<RaceEntryEditFormValues>({
			url: `/races/${raceUid}/entries/${entryId}`,
			method: "put",
			onSuccess: () => {
				toast.success("出走馬を更新しました");
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
		submit(values);
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
		/>
	);
};

export default RaceEntryEditFormContainer;
