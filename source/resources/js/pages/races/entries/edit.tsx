import { Head, usePage } from "@inertiajs/react";
import RaceEntryEditFormContainer from "@/features/raceEntry/containers/RaceEntryEditFormContainer";
import type {
	RaceEntryEditFormValues,
	RaceInfo,
} from "@/features/raceEntry/presentational/RaceEntryEditForm/types";

type RacesEntriesEditProps = {
	race_uid: string;
	entry_uid: string;
	race_info: RaceInfo;
	initial_values: RaceEntryEditFormValues;
};

const RacesEntriesEdit = () => {
	const { race_uid, entry_uid, race_info, initial_values } =
		usePage<RacesEntriesEditProps>().props;

	return (
		<>
			<Head title="出走馬編集" />
			<RaceEntryEditFormContainer
				raceUid={race_uid}
				entryUid={entry_uid}
				raceInfo={race_info}
				initialValues={initial_values}
			/>
		</>
	);
};

export default RacesEntriesEdit;
