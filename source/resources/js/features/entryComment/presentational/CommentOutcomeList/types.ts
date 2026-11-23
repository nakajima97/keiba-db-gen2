import type { CommentOutcome, EntryComment } from "../../types";

export type CommentOutcomeListProps = {
	/** 出走馬のコメント一覧。出馬表データがなく出走馬と対応付けられない場合は null */
	comments: EntryComment[] | null;
	/** 当否を保存中のコメント（`${speaker_type}-${id}`） */
	pendingKeys: string[];
	onOutcomeChange: (comment: EntryComment, outcome: CommentOutcome | null) => void;
};
