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
import type { BalanceDashboardProps } from "./types";

export default function BalanceDashboard({
	selectedYear,
	availableYears,
	summary,
	dailyBalances,
	onYearChange,
}: BalanceDashboardProps) {
	return (
		<div className="flex flex-col gap-6 p-4">
			<div className="flex items-center justify-between">
				<h1 className="text-xl font-semibold">収支ダッシュボード</h1>
				<Select
					value={String(selectedYear)}
					onValueChange={(value) => onYearChange(Number(value))}
				>
					<SelectTrigger className="w-32">
						<SelectValue placeholder="年を選択" />
					</SelectTrigger>
					<SelectContent>
						{availableYears.map((year) => (
							<SelectItem key={year} value={String(year)}>
								{year}年
							</SelectItem>
						))}
					</SelectContent>
				</Select>
			</div>

			{summary !== null ? (
				<div className="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
					<Card>
						<CardHeader>
							<CardTitle className="text-sm text-muted-foreground">
								合計購入額
							</CardTitle>
						</CardHeader>
						<CardContent>
							<p className="text-2xl font-bold">
								¥{summary.total_purchase_amount.toLocaleString()}
							</p>
						</CardContent>
					</Card>
					<Card>
						<CardHeader>
							<CardTitle className="text-sm text-muted-foreground">
								合計払い戻し額
							</CardTitle>
						</CardHeader>
						<CardContent>
							<p className="text-2xl font-bold">
								¥{summary.total_payout_amount.toLocaleString()}
							</p>
						</CardContent>
					</Card>
					<Card>
						<CardHeader>
							<CardTitle className="text-sm text-muted-foreground">
								プラスマイナス
							</CardTitle>
						</CardHeader>
						<CardContent>
							<p
								className={`text-2xl font-bold ${
									summary.total_net_amount >= 0
										? "text-green-600"
										: "text-red-600"
								}`}
							>
								{summary.total_net_amount >= 0 ? "+" : ""}¥
								{summary.total_net_amount.toLocaleString()}
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
									summary.total_return_rate >= 100
										? "text-green-600"
										: "text-red-600"
								}`}
							>
								{summary.total_return_rate.toFixed(1)}%
							</p>
						</CardContent>
					</Card>
				</div>
			) : (
				<div className="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
					{(
						[
							"合計購入額",
							"合計払い戻し額",
							"プラスマイナス",
							"回収率",
						] as const
					).map((label) => (
						<Card key={label}>
							<CardHeader>
								<CardTitle className="text-sm text-muted-foreground">
									{label}
								</CardTitle>
							</CardHeader>
							<CardContent>
								<p className="text-2xl font-bold text-muted-foreground">-</p>
							</CardContent>
						</Card>
					))}
				</div>
			)}

			<div className="flex flex-col gap-2">
				<h2 className="text-base font-semibold">日次収支</h2>
				{dailyBalances.length === 0 ? (
					<div className="flex items-center justify-center py-16 text-muted-foreground">
						<p>記録がありません</p>
					</div>
				) : (
					<div className="overflow-hidden rounded-xl border">
						<table className="w-full text-sm">
							<thead>
								<tr className="border-b bg-muted/50">
									<th className="px-4 py-3 text-left font-medium text-muted-foreground">
										日付
									</th>
									<th className="px-4 py-3 text-right font-medium text-muted-foreground">
										購入金額
									</th>
									<th className="px-4 py-3 text-right font-medium text-muted-foreground">
										払い戻し金額
									</th>
									<th className="px-4 py-3 text-right font-medium text-muted-foreground">
										プラスマイナス
									</th>
									<th className="px-4 py-3 text-right font-medium text-muted-foreground">
										回収率
									</th>
								</tr>
							</thead>
							<tbody>
								{dailyBalances.map((row) => (
									<tr
										key={row.date}
										className="border-b last:border-0 hover:bg-muted/30"
									>
										<td className="px-4 py-3">{formatDateDisplay(row.date)}</td>
										<td className="px-4 py-3 text-right">
											¥{row.purchase_amount.toLocaleString()}
										</td>
										<td className="px-4 py-3 text-right">
											¥{row.payout_amount.toLocaleString()}
										</td>
										<td
											className={`px-4 py-3 text-right font-medium ${
												row.net_amount >= 0 ? "text-green-600" : "text-red-600"
											}`}
										>
											{row.net_amount >= 0 ? "+" : ""}¥
											{row.net_amount.toLocaleString()}
										</td>
										<td
											className={`px-4 py-3 text-right ${
												row.return_rate >= 100
													? "text-green-600"
													: "text-red-600"
											}`}
										>
											{row.return_rate.toFixed(1)}%
										</td>
									</tr>
								))}
							</tbody>
						</table>
					</div>
				)}
			</div>
		</div>
	);
}

export type {
	BalanceDashboardProps,
	DailyBalance,
	YearlySummary,
} from "./types";
