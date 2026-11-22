import type {
	CommentOutcome,
	EntryComment,
} from "@/features/entryComment/types";
import type { RaceMyTicket } from "@/features/raceResult/presentational/RaceMyTicketSection";

export type PayoutEntry = {
	ticket_type_label: string;
	ticket_type_name: string;
	payout_amount: number;
	popularity: number;
	horses: Array<{
		horse_number: number;
		sort_order: number;
	}>;
};

export type FinishingHorseNote = {
	id?: number;
	content: string;
	source: "race" | "horse";
};

export type FinishingHorse = {
	finishing_order: number;
	frame_number: number;
	horse_number: number;
	horse_id: number | null;
	horse_uid: string | null;
	horse_name: string;
	jockey_name: string;
	race_time: string;
	note?: FinishingHorseNote | null;
	/** 馬番で対応付けた出走馬の ID。出馬表データがなければ null */
	race_entry_id?: number | null;
	/** 出走馬に対する自分の調教師・騎手コメント。出馬表データがなければ null */
	comments?: EntryComment[] | null;
};

export type RaceResultDetailProps = {
	race: {
		uid: string;
		venue_name: string;
		race_date: string;
		race_number: number;
		payouts: PayoutEntry[];
		finishing_horses: FinishingHorse[];
	};
	tickets?: RaceMyTicket[];
	onNoteClick?: (horseId: number) => void;
	onDeleteClick?: () => void;
	/** 当否を保存中のコメント（commentKey） */
	pendingCommentKeys?: string[];
	onCommentOutcomeChange?: (params: {
		raceEntryId: number;
		comment: EntryComment;
		outcome: CommentOutcome | null;
	}) => void;
};
