import type {
	CommentOutcome,
	CommentSentiment,
	CommentSpeakerType,
	EntryComment,
} from "../../types";

/** 追加・編集フォームの入力値 */
export type EntryCommentFormValues = {
	speakerType: CommentSpeakerType;
	sentiment: CommentSentiment;
	content: string;
	outcome: CommentOutcome | null;
};

export type EntryCommentModalProps = {
	open: boolean;
	horseName: string;
	/** 出走情報の調教師名。未登録なら null（発言者に「調教師」を選べない） */
	trainerName: string | null;
	jockeyName: string;
	/** その出走馬の自分のコメント（登録が古い順） */
	comments: EntryComment[];
	/** 編集中のコメント（`${speaker_type}-${id}`）。null なら追加モード */
	editingKey: string | null;
	form: EntryCommentFormValues;
	contentMaxLength: number;
	errorMessage: string | null;
	/** フォームの保存中 */
	submitting: boolean;
	/** 一覧で当否の保存・削除を処理中のコメント（`${speaker_type}-${id}`） */
	pendingKeys: string[];
	onOpenChange: (open: boolean) => void;
	onFormChange: (values: Partial<EntryCommentFormValues>) => void;
	onSubmit: () => void;
	onEditClick: (comment: EntryComment) => void;
	onCancelEdit: () => void;
	onDeleteClick: (comment: EntryComment) => void;
	onOutcomeChange: (comment: EntryComment, outcome: CommentOutcome | null) => void;
};
