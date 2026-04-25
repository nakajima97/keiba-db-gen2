import { formatDateDisplay } from "@/utils/date";
import type { RaceResultDetailProps } from "./types";
import { formatHorseNumbers } from "./utils";

export default function RaceResultDetail({ race }: RaceResultDetailProps) {
	return (
		<div className="flex flex-col gap-4 p-4">
			<div>
				<h1 className="text-xl font-semibold">レース結果</h1>
				<p className="text-sm text-muted-foreground">
					{formatDateDisplay(race.race_date)} {race.venue_name}{" "}
					{race.race_number}R
				</p>
			</div>

			<div className="flex flex-col gap-2">
				<h2 className="text-base font-semibold">着順</h2>
				{race.finishing_horses.length === 0 ? (
					<p className="text-sm text-muted-foreground">
						着順データがありません
					</p>
				) : (
					<div className="overflow-x-auto rounded-xl border">
						<table className="w-full text-sm">
							<thead>
								<tr className="border-b bg-muted/50">
									<th className="px-4 py-3 text-left font-medium text-muted-foreground">
										着順
									</th>
									<th className="px-4 py-3 text-left font-medium text-muted-foreground">
										枠番
									</th>
									<th className="px-4 py-3 text-left font-medium text-muted-foreground">
										馬番
									</th>
									<th className="px-4 py-3 text-left font-medium text-muted-foreground">
										馬名
									</th>
									<th className="px-4 py-3 text-left font-medium text-muted-foreground">
										騎手
									</th>
									<th className="px-4 py-3 text-right font-medium text-muted-foreground">
										タイム
									</th>
								</tr>
							</thead>
							<tbody>
								{race.finishing_horses.map((horse) => (
									<tr
										key={horse.horse_number}
										className="border-b last:border-0 hover:bg-muted/30"
									>
										<td className="px-4 py-3">
											{horse.finishing_order}
										</td>
										<td className="px-4 py-3">
											{horse.frame_number}
										</td>
										<td className="px-4 py-3">
											{horse.horse_number}
										</td>
										<td className="px-4 py-3">
											{horse.horse_name}
										</td>
										<td className="px-4 py-3">
											{horse.jockey_name}
										</td>
										<td className="px-4 py-3 text-right">
											{horse.race_time}
										</td>
									</tr>
								))}
							</tbody>
						</table>
					</div>
				)}
			</div>

			<div className="overflow-x-auto rounded-xl border">
				<table className="w-full text-sm">
					<thead>
						<tr className="border-b bg-muted/50">
							<th className="px-4 py-3 text-left font-medium text-muted-foreground">
								券種
							</th>
							<th className="px-4 py-3 text-left font-medium text-muted-foreground">
								馬番
							</th>
							<th className="px-4 py-3 text-right font-medium text-muted-foreground">
								払戻金額
							</th>
							<th className="px-4 py-3 text-right font-medium text-muted-foreground">
								人気
							</th>
						</tr>
					</thead>
					<tbody>
						{race.payouts.map((payout) => (
							<tr
								key={`${payout.ticket_type_name}-${payout.horses.map((h) => h.horse_number).join("-")}`}
								className="border-b last:border-0 hover:bg-muted/30"
							>
								<td className="px-4 py-3">{payout.ticket_type_label}</td>
								<td className="px-4 py-3">
									{formatHorseNumbers(payout.horses, payout.ticket_type_name)}
								</td>
								<td className="px-4 py-3 text-right">
									¥{payout.payout_amount.toLocaleString()}
								</td>
								<td className="px-4 py-3 text-right">
									{payout.popularity}人気
								</td>
							</tr>
						))}
					</tbody>
				</table>
			</div>
		</div>
	);
}

export type { RaceResultDetailProps } from "./types";
