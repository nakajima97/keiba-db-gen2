import HorseDetail from "@/features/horseDetail/presentational/HorseDetail";
import type { HorseDetailItem } from "@/features/horseDetail/presentational/HorseDetail/types";
import HorseNotesListContainer from "@/features/horseNote/containers/HorseNotesListContainer";
import type { HorseNoteRaceOption } from "@/features/horseNote/presentational/HorseNoteModal/types";
import type { HorseNoteListItem } from "@/features/horseNote/presentational/HorseNotesList/types";
import { formatDateDisplay } from "@/utils/date";
import { Head, usePage } from "@inertiajs/react";

type HorseRaceInfo = {
	uid: string;
	race_date: string;
	venue_name: string;
	race_number: number;
	race_name: string | null;
};

type HorseNoteInertiaItem = {
	id: number;
	content: string;
	race: HorseRaceInfo | null;
	created_at: string;
	updated_at: string;
};

type HorseDetailWithNotes = HorseDetailItem & {
	notes?: HorseNoteInertiaItem[];
};

type HorsesShowProps = {
	horse: HorseDetailWithNotes;
};

const buildRaceLabel = (race: HorseRaceInfo): string => {
	const base = `${formatDateDisplay(race.race_date)} ${race.venue_name} ${race.race_number}R`;
	return race.race_name != null ? `${base} ${race.race_name}` : base;
};

const HorsesShow = () => {
	const { horse } = usePage<HorsesShowProps>().props;

	const noteItems: HorseNoteListItem[] = (horse.notes ?? []).map((note) => ({
		id: note.id,
		content: note.content,
		race:
			note.race != null
				? { uid: note.race.uid, label: buildRaceLabel(note.race) }
				: null,
		created_at: note.created_at,
		updated_at: note.updated_at,
	}));

	const raceOptions: HorseNoteRaceOption[] = horse.race_histories.map(
		(history) => ({
			id: history.race_id,
			uid: history.race_uid,
			label: buildRaceLabel({
				uid: history.race_uid,
				race_date: history.race_date,
				venue_name: history.venue_name,
				race_number: history.race_number,
				race_name: history.race_name,
			}),
		}),
	);

	return (
		<>
			<Head title={horse.name} />
			<HorseDetail horse={horse} />
			<div className="px-4 pb-4">
				<HorseNotesListContainer
					horseId={horse.id}
					horseName={horse.name}
					initialNotes={noteItems}
					raceOptions={raceOptions}
				/>
			</div>
		</>
	);
};

export default HorsesShow;
