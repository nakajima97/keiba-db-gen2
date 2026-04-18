import type { RaceDetailProps } from "./types";

export default function RaceDetail({ race }: RaceDetailProps) {
	return (
		<div className="flex flex-col gap-4 p-4">
			<h1 className="text-xl font-semibold">レース詳細</h1>

			<div className="overflow-x-auto rounded-xl border">
				<table className="w-full text-sm">
					<tbody>
						<tr className="border-b">
							<th className="w-32 bg-muted/50 px-4 py-3 text-left font-medium text-muted-foreground">
								開催日
							</th>
							<td className="px-4 py-3">
								{race.race_date.replace(/-/g, "/")}
							</td>
						</tr>
						<tr className="border-b">
							<th className="bg-muted/50 px-4 py-3 text-left font-medium text-muted-foreground">
								競馬場
							</th>
							<td className="px-4 py-3">{race.venue_name}</td>
						</tr>
						<tr>
							<th className="bg-muted/50 px-4 py-3 text-left font-medium text-muted-foreground">
								レース番号
							</th>
							<td className="px-4 py-3">{race.race_number}R</td>
						</tr>
					</tbody>
				</table>
			</div>

			<h2 className="text-lg font-semibold">出馬表</h2>

			<div className="overflow-x-auto rounded-xl border">
				<table className="w-full text-sm">
					<thead>
						<tr className="border-b bg-muted/50">
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
								騎手名
							</th>
							<th className="px-4 py-3 text-left font-medium text-muted-foreground">
								馬体重
							</th>
						</tr>
					</thead>
					<tbody>
						{race.entries.map((entry) => (
							<tr
								key={entry.horse_number}
								className="border-b last:border-0"
							>
								<td className="px-4 py-3">{entry.frame_number}</td>
								<td className="px-4 py-3">{entry.horse_number}</td>
								<td className="px-4 py-3">{entry.horse_name}</td>
								<td className="px-4 py-3">{entry.jockey_name}</td>
								<td className="px-4 py-3">
									{entry.weight !== null ? `${entry.weight}kg` : "-"}
								</td>
							</tr>
						))}
					</tbody>
				</table>
			</div>
		</div>
	);
}

export type { RaceDetailItem, RaceDetailProps } from "./types";
