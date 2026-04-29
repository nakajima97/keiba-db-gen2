export type MarkValue = "◎" | "○" | "▲" | "△" | "×" | "✓";

export const MARK_VALUES: readonly MarkValue[] = [
	"◎",
	"○",
	"▲",
	"△",
	"×",
	"✓",
] as const;

export type RaceMarkColumnType = "own" | "other";

export type RaceMarkColumn = {
	id: number;
	type: RaceMarkColumnType;
	label: string | null;
	display_order: number;
};

export type RaceMarkValue = {
	column_id: number;
	race_entry_id: number;
	mark_value: MarkValue;
};

export type RaceEntryNote = {
	id?: number;
	content: string;
	source: "race" | "horse";
};

export type RaceEntry = {
	id: number;
	frame_number: number;
	horse_number: number;
	horse_id: number;
	horse_name: string;
	jockey_name: string;
	weight: number | null;
	note?: RaceEntryNote | null;
};

export type RaceDetailItem = {
	uid: string;
	race_date: string;
	venue_name: string;
	race_number: number;
	entries: RaceEntry[];
	mark_columns: RaceMarkColumn[];
	marks: RaceMarkValue[];
};

export type RaceDetailProps = {
	race: RaceDetailItem;
	onMarkChange: (params: {
		columnId: number;
		raceEntryId: number;
		markValue: MarkValue | null;
	}) => void;
	onAddOtherColumn: () => void;
	onRemoveOtherColumn: (columnId: number) => void;
	onChangeColumnLabel: (columnId: number, label: string) => void;
	onNoteClick?: (horseId: number) => void;
};
