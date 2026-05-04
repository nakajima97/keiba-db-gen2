import { Head, usePage } from "@inertiajs/react";
import RaceEntryEditFormContainer from "@/features/raceEntry/containers/RaceEntryEditFormContainer";
import type {
	RaceEntryEditFormValues,
	RaceInfo,
} from "@/features/raceEntry/presentational/RaceEntryEditForm/types";

type RacesEntriesEditProps = {
	race_uid: string;
	entry_id: number;
	race_info: RaceInfo;
	initial_values: RaceEntryEditFormValues;
};

const RacesEntriesEdit = () => {
	const { race_uid, entry_id, race_info, initial_values } =
		usePage<RacesEntriesEditProps>().props;

	return (
		<>
			<Head title="出走馬編集" />
			<RaceEntryEditFormContainer
				raceUid={race_uid}
				entryId={entry_id}
				raceInfo={race_info}
				initialValues={initial_values}
			/>
		</>
	);
};

export default RacesEntriesEdit;
