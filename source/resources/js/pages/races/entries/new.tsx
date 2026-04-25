import { Head } from "@inertiajs/react";
import RaceEntryRegistrationForm from "@/features/raceEntry/presentational/RaceEntryRegistrationForm";

export default function RacesEntriesNew() {
	return (
		<>
			<Head title="出走馬登録" />
			<RaceEntryRegistrationForm
				raceInfo={{
					race_date: "2026-04-26",
					venue_name: "東京",
					race_number: 11,
				}}
				pastedText=""
				isSubmitting={false}
				onPastedTextChange={() => {}}
				onSubmit={() => {}}
			/>
		</>
	);
}
