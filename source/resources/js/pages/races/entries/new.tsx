import { Head, usePage } from "@inertiajs/react";
import RaceEntryRegistrationFormContainer from "@/features/raceEntry/containers/RaceEntryRegistrationFormContainer";
import type { RaceInfo } from "@/features/raceEntry/presentational/RaceEntryRegistrationForm/types";

type RacesEntriesNewProps = {
	race_uid: string;
	race_info: RaceInfo;
};

const RacesEntriesNew = () => {
	const { race_uid, race_info } = usePage<RacesEntriesNewProps>().props;

	return (
		<>
			<Head title="出走馬登録" />
			<RaceEntryRegistrationFormContainer
				raceUid={race_uid}
				raceInfo={race_info}
			/>
		</>
	);
};

export default RacesEntriesNew;
