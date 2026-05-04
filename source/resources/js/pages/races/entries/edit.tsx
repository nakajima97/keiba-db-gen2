// TODO: コンテナ実装は実装フェーズで対応
import { Head } from "@inertiajs/react";
import RaceEntryEditForm from "@/features/raceEntry/presentational/RaceEntryEditForm";

const RacesEntriesEdit = () => {
	return (
		<>
			<Head title="出走馬編集" />
			<RaceEntryEditForm
				raceUid="test-race-uid-123"
				raceInfo={{
					race_date: "2026-04-26",
					venue_name: "東京",
					race_number: 11,
				}}
				values={{
					horse_name: "コントレイル",
					jockey_name: "福永祐一",
					frame_number: 2,
					horse_number: 3,
					weight: "57.0",
					horse_weight: "486",
				}}
				errors={{}}
				isSubmitting={false}
				onChange={() => {}}
				onSubmit={() => {}}
			/>
		</>
	);
};

export default RacesEntriesEdit;
