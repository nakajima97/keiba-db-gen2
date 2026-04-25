import type { RaceResultDetailProps } from "./types";
import { formatHorseNumbers } from "./utils";

export default function RaceResultDetail({ race }: RaceResultDetailProps) {
	return (
		<div className="flex flex-col gap-4 p-4">
			<div>
				<h1 className="text-xl font-semibold">レース結果</h1>
				<p className="text-sm text-muted-foreground">
					{race.race_date.replace(/-/g, "/")} {race.venue_name}{" "}
					{race.race_number}R
				</p>
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
						{race.payouts.map((payout, index) => (
							<tr
								key={index}
								className="border-b last:border-0 hover:bg-muted/30"
							>
								<td className="px-4 py-3">{payout.ticket_type_label}</td>
								<td className="px-4 py-3">
									{formatHorseNumbers(
										payout.horses,
										payout.ticket_type_name,
									)}
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
