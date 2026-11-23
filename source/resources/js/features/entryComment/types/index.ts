/** 発言者種別（調教師 / 騎手） */
export type CommentSpeakerType = "trainer" | "jockey";

/** コメントの向き（前向き / 条件付き / 弱気） */
export type CommentSentiment = "positive" | "conditional" | "negative";

/** 当否（当たり / 外れ / 判定不能）。未入力は null で表す */
export type CommentOutcome = "hit" | "miss" | "undeterminable";

/** 出走馬に対する調教師・騎手コメント（docs/specs/openapi.yaml の EntryComment） */
export type EntryComment = {
	id: number;
	speaker_type: CommentSpeakerType;
	speaker_id: number;
	speaker_name: string;
	sentiment: CommentSentiment;
	content: string;
	outcome: CommentOutcome | null;
	created_at: string;
};

/** 向き1つ分の的中率。judged は当たり + 外れ の件数 */
export type CommentHitRate = {
	hit: number;
	judged: number;
};

/** 発言者1人分の向き別の的中率 */
export type CommentSentimentStats = Record<CommentSentiment, CommentHitRate>;
