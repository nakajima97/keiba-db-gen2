import type {
	CommentOutcome,
	CommentSentiment,
	CommentSpeakerType,
} from "../types";

export const SPEAKER_TYPE_LABELS: Record<CommentSpeakerType, string> = {
	trainer: "調教師",
	jockey: "騎手",
};

export const SENTIMENT_LABELS: Record<CommentSentiment, string> = {
	positive: "前向き",
	conditional: "条件付き",
	negative: "弱気",
};

/** 的中率表示で使う向きの短縮ラベル（例: 前 3/10） */
export const SENTIMENT_SHORT_LABELS: Record<CommentSentiment, string> = {
	positive: "前",
	conditional: "条",
	negative: "弱",
};

export const OUTCOME_LABELS: Record<CommentOutcome, string> = {
	hit: "当たり",
	miss: "外れ",
	undeterminable: "判定不能",
};

export const SENTIMENTS: CommentSentiment[] = [
	"positive",
	"conditional",
	"negative",
];

export const OUTCOMES: CommentOutcome[] = ["hit", "miss", "undeterminable"];
