import { describe, expect, it } from "vitest";
import type { EntryComment } from "@/features/entryComment/types";
import type { RaceEntry } from "@/features/raceDetail/presentational/RaceDetail/types";
import { applyEntryCommentsChange } from "./applyEntryCommentsChange";

const emptyStats = {
	positive: { hit: 0, judged: 0 },
	conditional: { hit: 0, judged: 0 },
	negative: { hit: 0, judged: 0 },
};

const buildEntry = (overrides: Partial<RaceEntry>): RaceEntry => ({
	id: 1,
	uid: "uid",
	frame_number: 1,
	horse_number: 1,
	horse_id: 1,
	horse_uid: "h",
	horse_name: "馬",
	jockey_id: 100,
	jockey_name: "騎手",
	trainer_id: 200,
	weight: null,
	comments: [],
	trainer_comment_stats: emptyStats,
	jockey_comment_stats: emptyStats,
	...overrides,
});

const comment: EntryComment = {
	id: 1,
	speaker_type: "jockey",
	speaker_id: 100,
	speaker_name: "騎手",
	sentiment: "positive",
	content: "好感触",
	outcome: "hit",
	created_at: "2026-11-22T10:00:00+09:00",
};

const newStats = {
	positive: { hit: 1, judged: 1 },
	conditional: { hit: 0, judged: 0 },
	negative: { hit: 0, judged: 0 },
};

describe("applyEntryCommentsChange", () => {
	it("対象の出走馬だけ comments を置き換え、同じ調教師・騎手の出走馬すべてに的中率を反映する", () => {
		// Arrange
		const entries = [
			buildEntry({ id: 1, jockey_id: 100, trainer_id: 200 }),
			buildEntry({ id: 2, jockey_id: 100, trainer_id: 300 }),
			buildEntry({ id: 3, jockey_id: 101, trainer_id: 200 }),
			buildEntry({ id: 4, jockey_id: 102, trainer_id: 400 }),
		];

		// Act
		const result = applyEntryCommentsChange(entries, {
			raceEntryId: 1,
			comments: [comment],
			stats: { trainer_id: 200, trainer: newStats, jockey_id: 100, jockey: newStats },
		});

		// Assert
		expect(result.map((e) => e.comments?.length)).toEqual([1, 0, 0, 0]);
		expect(result.map((e) => e.jockey_comment_stats)).toEqual([
			newStats,
			newStats,
			emptyStats,
			emptyStats,
		]);
		expect(result.map((e) => e.trainer_comment_stats)).toEqual([
			newStats,
			emptyStats,
			newStats,
			emptyStats,
		]);
	});

	it("stats.trainer_id が null のとき調教師未登録の出走馬の的中率は変更しない", () => {
		// Arrange
		const entries = [buildEntry({ id: 1, trainer_id: null, trainer_comment_stats: null })];

		// Act
		const result = applyEntryCommentsChange(entries, {
			raceEntryId: 1,
			comments: [comment],
			stats: { trainer_id: null, trainer: null, jockey_id: 100, jockey: newStats },
		});

		// Assert
		expect(result[0].trainer_comment_stats).toBeNull();
		expect(result[0].jockey_comment_stats).toEqual(newStats);
	});
});
