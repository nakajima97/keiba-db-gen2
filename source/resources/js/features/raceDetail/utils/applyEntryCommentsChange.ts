import type { EntryCommentStats } from "@/features/entryComment/requests/entryComments";
import type { EntryComment } from "@/features/entryComment/types";
import type { RaceEntry } from "@/features/raceDetail/presentational/RaceDetail/types";

/**
 * コメント変更の結果を出馬表の出走馬一覧に反映する。
 * - comments は対象の出走馬（raceEntryId）だけを置き換える
 * - 的中率は全レース分の集計なので、同じ調教師・騎手の出走馬すべてに stats を反映する
 */
export const applyEntryCommentsChange = (
	entries: RaceEntry[],
	params: {
		raceEntryId: number;
		comments: EntryComment[];
		stats: EntryCommentStats;
	},
): RaceEntry[] =>
	entries.map((entry) => {
		let next = entry;
		if (entry.id === params.raceEntryId) {
			next = { ...next, comments: params.comments };
		}
		if (
			params.stats.trainer_id !== null &&
			entry.trainer_id === params.stats.trainer_id
		) {
			next = { ...next, trainer_comment_stats: params.stats.trainer };
		}
		if (entry.jockey_id === params.stats.jockey_id) {
			next = { ...next, jockey_comment_stats: params.stats.jockey };
		}
		return next;
	});
