import type { EntryComment } from "../types";

/**
 * コメントを一意に識別するキー。ID は調教師・騎手のテーブルごとに採番されるため種別を含める。
 */
export const commentKey = (
	comment: Pick<EntryComment, "speaker_type" | "id">,
): string => `${comment.speaker_type}-${comment.id}`;

/**
 * コメント一覧の要素を置き換える。種別変更で ID が変わった場合も元の位置に置く。
 */
export const replaceComment = (
	comments: EntryComment[],
	targetKey: string,
	next: EntryComment,
): EntryComment[] =>
	comments.map((comment) => (commentKey(comment) === targetKey ? next : comment));
