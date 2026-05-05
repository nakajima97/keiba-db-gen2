import { Link } from "@inertiajs/react";
import ScrollableTable from "@/components/presentational/ScrollableTable";
import {
	Card,
	CardContent,
	CardHeader,
	CardTitle,
} from "@/components/shadcn/ui/card";
import {
	Select,
	SelectContent,
	SelectItem,
	SelectTrigger,
	SelectValue,
} from "@/components/shadcn/ui/select";
import { formatDateDisplay } from "@/utils/date";
import type {
	InsightsPattern,
	InsightsPeriod,
	InsightsPopularityBand,
	InsightsViewProps,
} from "./types";

const PERIOD_OPTIONS: { value: InsightsPeriod; label: string }[] = [
	{ value: "1m", label: "直近1ヶ月" },
	{ value: "3m", label: "直近3ヶ月" },
	{ value: "6m", label: "直近6ヶ月" },
	{ value: "1y", label: "直近1年" },
	{ value: "all", label: "全期間" },
];

const PATTERN_LABELS: Record<InsightsPattern, string> = {
	hit: "的中",
	axis_only: "軸だけが1〜2着",
	others_only: "相手だけが1〜2着",
	miss: "外れ",
};

const PATTERN_BAR_COLORS: Record<InsightsPattern, string> = {
	hit: "bg-green-500",
	axis_only: "bg-yellow-500",
	others_only: "bg-blue-500",
	miss: "bg-gray-400",
};

const PATTERN_TEXT_COLORS: Record<InsightsPattern, string> = {
	hit: "text-green-600",
	axis_only: "text-yellow-700",
	others_only: "text-blue-600",
	miss: "text-muted-foreground",
};

const POPULARITY_LABELS: Record<InsightsPopularityBand, string> = {
	top: "1〜3番人気",
	mid: "4〜6番人気",
	low: "7番人気以下",
};

const PATTERN_ORDER: InsightsPattern[] = [
	"hit",
	"axis_only",
	"others_only",
	"miss",
];
const POPULARITY_ORDER: InsightsPopularityBand[] = ["top", "mid", "low"];

const formatYen = (value: number): string => `¥${value.toLocaleString()}`;
const formatPercent = (value: number): string => `${value.toFixed(1)}%`;

const InsightsView = ({
	period,
	onPeriodChange,
	summary,
	patternBreakdown,
	popularityPatternMatrix,
	popularityReturns,
	monthlyTrends,
	recentSamples,
}: InsightsViewProps) => {
	const hasData = summary !== null && summary.total_tickets > 0;

	return (
		<div className="flex flex-col gap-6 p-4">
			<div className="flex items-center justify-between gap-4">
				<div className="flex flex-col gap-1">
					<h1 className="text-xl font-semibold">振り返り（馬連流し）</h1>
					<p className="text-sm text-muted-foreground">
						結果が入力済みの馬連流しを対象に、的中・回収のパターンを集計します。
					</p>
				</div>
				<Select
					value={period}
					onValueChange={(value) => onPeriodChange(value as InsightsPeriod)}
				>
					<SelectTrigger className="w-40">
						<SelectValue placeholder="期間を選択" />
					</SelectTrigger>
					<SelectContent>
						{PERIOD_OPTIONS.map((opt) => (
							<SelectItem key={opt.value} value={opt.value}>
								{opt.label}
							</SelectItem>
						))}
					</SelectContent>
				</Select>
			</div>

			{!hasData ? (
				<div className="flex items-center justify-center py-16 text-muted-foreground">
					<p>該当する馬連流しの記録がありません</p>
				</div>
			) : (
				<>
					{/* 1. サマリー */}
					<section className="flex flex-col gap-2">
						<h2 className="text-base font-semibold">サマリー</h2>
						<div className="grid gap-4 md:grid-cols-2 lg:grid-cols-5">
							<Card>
								<CardHeader>
									<CardTitle className="text-sm text-muted-foreground">
										対象件数
									</CardTitle>
								</CardHeader>
								<CardContent>
									<p className="text-2xl font-bold">
										{summary.total_tickets.toLocaleString()}件
									</p>
								</CardContent>
							</Card>
							<Card>
								<CardHeader>
									<CardTitle className="text-sm text-muted-foreground">
										総購入額
									</CardTitle>
								</CardHeader>
								<CardContent>
									<p className="text-2xl font-bold">
										{formatYen(summary.total_purchase_amount)}
									</p>
								</CardContent>
							</Card>
							<Card>
								<CardHeader>
									<CardTitle className="text-sm text-muted-foreground">
										総払戻額
									</CardTitle>
								</CardHeader>
								<CardContent>
									<p className="text-2xl font-bold">
										{formatYen(summary.total_payout_amount)}
									</p>
								</CardContent>
							</Card>
							<Card>
								<CardHeader>
									<CardTitle className="text-sm text-muted-foreground">
										回収率
									</CardTitle>
								</CardHeader>
								<CardContent>
									<p
										className={`text-2xl font-bold ${
											summary.return_rate >= 100
												? "text-green-600"
												: "text-red-600"
										}`}
									>
										{formatPercent(summary.return_rate)}
									</p>
								</CardContent>
							</Card>
							<Card>
								<CardHeader>
									<CardTitle className="text-sm text-muted-foreground">
										的中率
									</CardTitle>
								</CardHeader>
								<CardContent>
									<p className="text-2xl font-bold">
										{formatPercent(summary.hit_rate)}
									</p>
								</CardContent>
							</Card>
						</div>
					</section>

					{/* 2. 4パターン分解 */}
					<section className="flex flex-col gap-2">
						<h2 className="text-base font-semibold">当落パターン分解</h2>
						<Card>
							<CardContent className="flex flex-col gap-3 pt-6">
								{PATTERN_ORDER.map((pattern) => {
									const row = patternBreakdown.find(
										(r) => r.pattern === pattern,
									);
									const count = row?.count ?? 0;
									const ratio = row?.ratio ?? 0;
									return (
										<div key={pattern} className="flex flex-col gap-1">
											<div className="flex items-center justify-between text-sm">
												<span className={PATTERN_TEXT_COLORS[pattern]}>
													{PATTERN_LABELS[pattern]}
												</span>
												<span className="font-medium">
													{count.toLocaleString()}件 ({formatPercent(ratio)})
												</span>
											</div>
											<div className="h-2 w-full overflow-hidden rounded bg-muted">
												<div
													className={`h-full ${PATTERN_BAR_COLORS[pattern]}`}
													style={{ width: `${Math.min(ratio, 100)}%` }}
												/>
											</div>
										</div>
									);
								})}
							</CardContent>
						</Card>
					</section>

					{/* 3. 軸馬の人気帯 × 4パターン（クロス集計） */}
					<section className="flex flex-col gap-2">
						<h2 className="text-base font-semibold">
							軸馬の人気帯 × 当落パターン
						</h2>
						<ScrollableTable>
							<thead>
								<tr className="border-b bg-muted/50">
									<th className="px-4 py-3 text-left font-medium text-muted-foreground">
										人気帯
									</th>
									{PATTERN_ORDER.map((pattern) => (
										<th
											key={pattern}
											className="px-4 py-3 text-right font-medium text-muted-foreground"
										>
											{PATTERN_LABELS[pattern]}
										</th>
									))}
								</tr>
							</thead>
							<tbody>
								{POPULARITY_ORDER.map((popularity) => (
									<tr
										key={popularity}
										className="border-b last:border-0 hover:bg-muted/30"
									>
										<td className="px-4 py-3 font-medium">
											{POPULARITY_LABELS[popularity]}
										</td>
										{PATTERN_ORDER.map((pattern) => {
											const cell = popularityPatternMatrix.find(
												(c) =>
													c.popularity === popularity && c.pattern === pattern,
											);
											const count = cell?.count ?? 0;
											const ratio = cell?.ratio ?? 0;
											return (
												<td
													key={pattern}
													className="px-4 py-3 text-right tabular-nums"
												>
													<div>{count.toLocaleString()}件</div>
													<div className="text-xs text-muted-foreground">
														{formatPercent(ratio)}
													</div>
												</td>
											);
										})}
									</tr>
								))}
							</tbody>
						</ScrollableTable>
					</section>

					{/* 4. 軸馬の人気帯 × 回収率 */}
					<section className="flex flex-col gap-2">
						<h2 className="text-base font-semibold">軸馬の人気帯 × 回収率</h2>
						<ScrollableTable>
							<thead>
								<tr className="border-b bg-muted/50">
									<th className="px-4 py-3 text-left font-medium text-muted-foreground">
										人気帯
									</th>
									<th className="px-4 py-3 text-right font-medium text-muted-foreground">
										件数
									</th>
									<th className="px-4 py-3 text-right font-medium text-muted-foreground">
										購入額
									</th>
									<th className="px-4 py-3 text-right font-medium text-muted-foreground">
										払戻額
									</th>
									<th className="px-4 py-3 text-right font-medium text-muted-foreground">
										回収率
									</th>
								</tr>
							</thead>
							<tbody>
								{POPULARITY_ORDER.map((popularity) => {
									const row = popularityReturns.find(
										(r) => r.popularity === popularity,
									);
									const count = row?.count ?? 0;
									const purchase = row?.purchase_amount ?? 0;
									const payout = row?.payout_amount ?? 0;
									const rate = row?.return_rate ?? 0;
									return (
										<tr
											key={popularity}
											className="border-b last:border-0 hover:bg-muted/30"
										>
											<td className="px-4 py-3 font-medium">
												{POPULARITY_LABELS[popularity]}
											</td>
											<td className="px-4 py-3 text-right tabular-nums">
												{count.toLocaleString()}
											</td>
											<td className="px-4 py-3 text-right tabular-nums">
												{formatYen(purchase)}
											</td>
											<td className="px-4 py-3 text-right tabular-nums">
												{formatYen(payout)}
											</td>
											<td
												className={`px-4 py-3 text-right tabular-nums font-medium ${
													rate >= 100 ? "text-green-600" : "text-red-600"
												}`}
											>
												{formatPercent(rate)}
											</td>
										</tr>
									);
								})}
							</tbody>
						</ScrollableTable>
					</section>

					{/* 5. 月別の予想精度推移 */}
					<section className="flex flex-col gap-2">
						<h2 className="text-base font-semibold">月別の予想精度推移</h2>
						{monthlyTrends.length === 0 ? (
							<div className="flex items-center justify-center py-8 text-muted-foreground">
								<p>月別の記録がありません</p>
							</div>
						) : (
							<ScrollableTable>
								<thead>
									<tr className="border-b bg-muted/50">
										<th className="px-4 py-3 text-left font-medium text-muted-foreground">
											月
										</th>
										<th className="px-4 py-3 text-right font-medium text-muted-foreground">
											件数
										</th>
										<th className="px-4 py-3 text-right font-medium text-muted-foreground">
											的中率
										</th>
										<th className="px-4 py-3 text-right font-medium text-muted-foreground">
											回収率
										</th>
									</tr>
								</thead>
								<tbody>
									{monthlyTrends.map((row) => (
										<tr
											key={row.month}
											className="border-b last:border-0 hover:bg-muted/30"
										>
											<td className="px-4 py-3">{row.month}</td>
											<td className="px-4 py-3 text-right tabular-nums">
												{row.count.toLocaleString()}
											</td>
											<td className="px-4 py-3 text-right tabular-nums">
												{formatPercent(row.hit_rate)}
											</td>
											<td
												className={`px-4 py-3 text-right tabular-nums font-medium ${
													row.return_rate >= 100
														? "text-green-600"
														: "text-red-600"
												}`}
											>
												{formatPercent(row.return_rate)}
											</td>
										</tr>
									))}
								</tbody>
							</ScrollableTable>
						)}
					</section>

					{/* 6. 直近サンプル一覧 */}
					<section className="flex flex-col gap-2">
						<h2 className="text-base font-semibold">直近サンプル一覧</h2>
						{recentSamples.length === 0 ? (
							<div className="flex items-center justify-center py-8 text-muted-foreground">
								<p>該当するサンプルがありません</p>
							</div>
						) : (
							<ScrollableTable>
								<thead>
									<tr className="border-b bg-muted/50">
										<th className="px-4 py-3 text-left font-medium text-muted-foreground">
											日付
										</th>
										<th className="px-4 py-3 text-left font-medium text-muted-foreground">
											レース場
										</th>
										<th className="px-4 py-3 text-left font-medium text-muted-foreground">
											R
										</th>
										<th className="px-4 py-3 text-left font-medium text-muted-foreground">
											軸
										</th>
										<th className="px-4 py-3 text-right font-medium text-muted-foreground">
											軸の最良着順
										</th>
										<th className="px-4 py-3 text-right font-medium text-muted-foreground">
											相手の最良着順
										</th>
										<th className="px-4 py-3 text-left font-medium text-muted-foreground">
											パターン
										</th>
										<th className="px-4 py-3 text-right font-medium text-muted-foreground">
											購入
										</th>
										<th className="px-4 py-3 text-right font-medium text-muted-foreground">
											払戻
										</th>
									</tr>
								</thead>
								<tbody>
									{recentSamples.map((sample) => (
										<tr
											key={sample.ticket_id}
											className="border-b last:border-0 hover:bg-muted/30"
										>
											<td className="px-4 py-3">
												<Link
													href={`/races/${sample.race_uid}`}
													className="block underline"
												>
													{formatDateDisplay(sample.race_date)}
												</Link>
											</td>
											<td className="px-4 py-3">{sample.venue_name}</td>
											<td className="px-4 py-3">{sample.race_number}</td>
											<td className="px-4 py-3 tabular-nums">
												{sample.axis_horse_numbers.join(", ")}
											</td>
											<td className="px-4 py-3 text-right tabular-nums">
												{sample.axis_best_finishing_order ?? "-"}
											</td>
											<td className="px-4 py-3 text-right tabular-nums">
												{sample.others_best_finishing_order ?? "-"}
											</td>
											<td
												className={`px-4 py-3 ${PATTERN_TEXT_COLORS[sample.pattern]}`}
											>
												{PATTERN_LABELS[sample.pattern]}
											</td>
											<td className="px-4 py-3 text-right tabular-nums">
												{formatYen(sample.purchase_amount)}
											</td>
											<td className="px-4 py-3 text-right tabular-nums">
												{formatYen(sample.payout_amount)}
											</td>
										</tr>
									))}
								</tbody>
							</ScrollableTable>
						)}
					</section>
				</>
			)}
		</div>
	);
};

export default InsightsView;

export type {
	InsightsMonthlyTrend,
	InsightsPattern,
	InsightsPatternBreakdown,
	InsightsPeriod,
	InsightsPopularityBand,
	InsightsPopularityPatternCell,
	InsightsPopularityReturn,
	InsightsRecentSample,
	InsightsSummary,
	InsightsViewProps,
} from "./types";
