import { Badge } from "@/components/shadcn/ui/badge";
import CommentOutcomeSelect from "../CommentOutcomeSelect";
import { commentKey } from "../../utils/commentKey";
import { SENTIMENT_LABELS, SPEAKER_TYPE_LABELS } from "../../utils/labels";
import type { CommentOutcomeListProps } from "./types";

/**
 * レース結果確認・編集画面の着順テーブル「コメント当否」列のセル。
 * コメントごとに 発言者・向き・本文の抜粋 と当否セレクトを並べる。
 */
const CommentOutcomeList = ({
	comments,
	pendingKeys,
	onOutcomeChange,
}: CommentOutcomeListProps) => {
	if (comments === null || comments.length === 0) {
		return <span className="text-muted-foreground">-</span>;
	}

	return (
		<ul className="flex flex-col gap-2">
			{comments.map((comment) => {
				const key = commentKey(comment);
				return (
					<li key={key} className="flex items-center gap-2">
						<Badge variant="outline" className="shrink-0">
							{SPEAKER_TYPE_LABELS[comment.speaker_type]}
						</Badge>
						<Badge variant="secondary" className="shrink-0">
							{SENTIMENT_LABELS[comment.sentiment]}
						</Badge>
						<span
							className="min-w-0 max-w-[16rem] flex-1 truncate text-sm"
							title={comment.content}
						>
							{comment.content}
						</span>
						<CommentOutcomeSelect
							value={comment.outcome}
							ariaLabel={`${comment.speaker_name}のコメント「${comment.content}」の当否`}
							disabled={pendingKeys.includes(key)}
							onChange={(outcome) => onOutcomeChange(comment, outcome)}
						/>
					</li>
				);
			})}
		</ul>
	);
};

export default CommentOutcomeList;

export type { CommentOutcomeListProps } from "./types";
