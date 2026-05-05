/**
 * 振り返りページ（馬連流し）の型定義。
 *
 * 集計対象は `buy_type=nagashi` × `ticket_type=umaren` の馬券で、
 * レース結果（race_result_horses）が入力済みのもののみ。
 */

/**
 * 期間フィルタのプリセット値。
 * - `1m`: 直近1ヶ月（デフォルト）
 * - `3m`: 直近3ヶ月
 * - `6m`: 直近6ヶ月
 * - `1y`: 直近1年
 * - `all`: 全期間
 */
export type InsightsPeriod = "1m" | "3m" | "6m" | "1y" | "all";

/**
 * 4パターン分類。
 * - `hit`: 的中（軸が1〜2着 AND 相手から1頭が1〜2着）
 * - `axis_only`: 軸だけが1〜2着（相手は1〜2着にゼロ）
 * - `others_only`: 相手だけが1〜2着（軸は1〜2着に入らず）
 * - `miss`: 外れ（軸も相手も1〜2着に入らず）
 */
export type InsightsPattern = "hit" | "axis_only" | "others_only" | "miss";

/**
 * 軸馬の人気帯。
 * - `top`: 1〜3番人気
 * - `mid`: 4〜6番人気
 * - `low`: 7番人気以下
 */
export type InsightsPopularityBand = "top" | "mid" | "low";

/**
 * サマリーカード用データ。
 */
export type InsightsSummary = {
	/** 集計対象の馬券件数（buy_type=nagashi × ticket_type=umaren、結果入力済みに限る） */
	total_tickets: number;
	/** 総購入額（円） */
	total_purchase_amount: number;
	/** 総払戻額（円） */
	total_payout_amount: number;
	/** 回収率（%）= total_payout_amount / total_purchase_amount * 100 */
	return_rate: number;
	/** 的中率（%）= 的中件数 / total_tickets * 100 */
	hit_rate: number;
};

/**
 * 4パターン分解の集計値。
 */
export type InsightsPatternBreakdown = {
	pattern: InsightsPattern;
	count: number;
	/** 全件に対する割合（%） */
	ratio: number;
};

/**
 * 軸の人気帯 × 4パターンのクロス集計。
 */
export type InsightsPopularityPatternCell = {
	popularity: InsightsPopularityBand;
	pattern: InsightsPattern;
	count: number;
	/** 同人気帯内での割合（%） */
	ratio: number;
};

/**
 * 軸の人気帯 × 回収率。
 */
export type InsightsPopularityReturn = {
	popularity: InsightsPopularityBand;
	count: number;
	purchase_amount: number;
	payout_amount: number;
	/** 回収率（%）= payout_amount / purchase_amount * 100 */
	return_rate: number;
};

/**
 * 月別の予想精度推移。
 */
export type InsightsMonthlyTrend = {
	/** `YYYY-MM` 形式 */
	month: string;
	count: number;
	/** 的中率（%） */
	hit_rate: number;
	/** 回収率（%） */
	return_rate: number;
};

/**
 * 直近サンプル一覧の1行分。
 */
export type InsightsRecentSample = {
	ticket_id: number;
	race_uid: string;
	race_date: string;
	venue_name: string;
	race_number: number;
	/** 軸馬番（複数頭の場合はカンマ区切り） */
	axis_horse_numbers: number[];
	/** 軸馬の最終着順（複数頭の最良着順）。未確定の場合は null */
	axis_best_finishing_order: number | null;
	/** 相手馬から最も上位だった馬の着順。1頭も入線結果がない場合は null */
	others_best_finishing_order: number | null;
	pattern: InsightsPattern;
	purchase_amount: number;
	payout_amount: number;
};

/**
 * InsightsView コンポーネントの props。
 *
 * 全データを受け取って表示するだけの presentational コンポーネント。
 * API 呼び出しや状態管理は親（Inertia ページ）側で行う。
 */
export type InsightsViewProps = {
	period: InsightsPeriod;
	onPeriodChange: (period: InsightsPeriod) => void;
	summary: InsightsSummary | null;
	patternBreakdown: InsightsPatternBreakdown[];
	popularityPatternMatrix: InsightsPopularityPatternCell[];
	popularityReturns: InsightsPopularityReturn[];
	monthlyTrends: InsightsMonthlyTrend[];
	recentSamples: InsightsRecentSample[];
};
