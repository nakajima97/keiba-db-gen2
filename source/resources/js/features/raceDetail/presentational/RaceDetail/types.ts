import type {
	CommentSentimentStats,
	EntryComment,
} from "@/features/entryComment/types";
import type {
	RaceNomination,
	RaceNominationColumnMarkValue,
	RaceNominationColumnMemo,
	RaceNominationMarkColumn,
	RaceNominationStatus,
} from "@/features/raceNomination/types/raceNomination";

export type MarkValue = "◎" | "○" | "▲" | "△" | "☆" | "✓";

export const MARK_VALUES: readonly MarkValue[] = [
	"◎",
	"○",
	"▲",
	"△",
	"☆",
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

export type RaceMarkMemo = {
	column_id: number;
	race_entry_id: number;
	content: string;
};

export type RaceEntryNote = {
	id?: number;
	content: string;
	source: "race" | "horse";
};

export type RaceEntryTrainerNote = {
	id?: number;
	content: string;
};

export type RaceEntry = {
	id: number;
	uid: string;
	frame_number: number;
	horse_number: number;
	horse_id: number;
	horse_uid: string;
	horse_name: string;
	jockey_id?: number;
	jockey_name: string;
	trainer_id?: number | null;
	trainer_name?: string | null;
	trainer_affiliation?: string | null;
	trainer_note?: RaceEntryTrainerNote | null;
	weight: number | null;
	note?: RaceEntryNote | null;
	comments?: EntryComment[];
	trainer_comment_stats?: CommentSentimentStats | null;
	jockey_comment_stats?: CommentSentimentStats;
};

export type RaceDetailItem = {
	uid: string;
	race_date: string;
	venue_name: string;
	race_number: number;
	race_name: string | null;
	entries: RaceEntry[];
	mark_columns: RaceMarkColumn[];
	marks: RaceMarkValue[];
	mark_memos?: RaceMarkMemo[];
	nominations?: RaceNomination[];
	/** 登録馬用の他人の印列（出馬表の mark_columns とは独立） */
	nomination_mark_columns?: RaceNominationMarkColumn[];
	nomination_marks?: RaceNominationColumnMarkValue[];
	nomination_mark_memos?: RaceNominationColumnMemo[];
	has_result: boolean;
};

export type RaceDetailProps = {
	race: RaceDetailItem;
	/** 登録馬で付けたチェック／消しを horse_id で引き継いで出馬表に表示する（表示のみ） */
	nominationStatusByHorseId?: Record<number, RaceNominationStatus>;
	onMarkChange: (params: {
		columnId: number;
		raceEntryId: number;
		markValue: MarkValue | null;
	}) => void;
	onAddOtherColumn: () => void;
	onRemoveOtherColumn: (columnId: number) => void;
	onChangeColumnLabel: (columnId: number, label: string) => void;
	onNoteClick?: (horseId: number) => void;
	onTrainerNoteClick?: (trainerId: number) => void;
	onCommentClick?: (raceEntryId: number) => void;
	onMarkMemoClick?: (params: {
		columnId: number;
		raceEntryId: number;
	}) => void;
};
