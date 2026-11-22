import { SENTIMENTS, SENTIMENT_SHORT_LABELS } from "../../utils/labels";
import type { CommentStatsLabelProps } from "./types";

/**
 * 騎手名・調教師名の横に出す向き別の的中率（例: 前 3/10・条 5/8・弱 4/4）。
 * 判定済み件数が 0 の向きは出さず、すべて 0 件なら何も描画しない。
 */
const CommentStatsLabel = ({ stats }: CommentStatsLabelProps) => {
	if (stats === null) return null;
	const parts = SENTIMENTS.filter((s) => stats[s].judged > 0).map(
		(s) => `${SENTIMENT_SHORT_LABELS[s]} ${stats[s].hit}/${stats[s].judged}`,
	);
	if (parts.length === 0) return null;

	return (
		<span className="whitespace-nowrap text-xs text-muted-foreground">
			{parts.join("・")}
		</span>
	);
};

export default CommentStatsLabel;

export type { CommentStatsLabelProps } from "./types";
