import { Head, usePage } from "@inertiajs/react";
import RaceEntryAddFormContainer from "@/features/raceEntry/containers/RaceEntryAddFormContainer";
import type { RaceInfo } from "@/features/raceEntry/presentational/RaceEntryEditForm/types";

type RacesEntriesAddProps = {
	race_uid: string;
	race_info: RaceInfo;
};

const RacesEntriesAdd = () => {
	const { race_uid, race_info } = usePage<RacesEntriesAddProps>().props;

	return (
		<>
			<Head title="出走馬個別追加" />
			<RaceEntryAddFormContainer raceUid={race_uid} raceInfo={race_info} />
		</>
	);
};

export default RacesEntriesAdd;
