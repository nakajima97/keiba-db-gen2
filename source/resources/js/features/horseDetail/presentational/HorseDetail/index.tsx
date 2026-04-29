import ScrollableTable from "@/components/presentational/ScrollableTable";
import { formatDateDisplay } from "@/utils/date";
import type { HorseDetailProps } from "./types";

const HorseDetail = ({ horse }: HorseDetailProps) => {
	return (
		<div className="flex flex-col gap-4 p-4">
			<h1 className="text-xl font-semibold">競走馬詳細</h1>

			<ScrollableTable>
				<tbody>
					<tr className="border-b">
						<th
							scope="row"
							className="w-32 bg-muted/50 px-4 py-3 text-left font-medium text-muted-foreground"
						>
							馬名
						</th>
						<td className="px-4 py-3">{horse.name}</td>
					</tr>
					<tr>
						<th
							scope="row"
							className="bg-muted/50 px-4 py-3 text-left font-medium text-muted-foreground"
						>
							生年
						</th>
						<td className="px-4 py-3">
							{horse.birth_year !== null ? `${horse.birth_year}年` : "—"}
						</td>
					</tr>
				</tbody>
			</ScrollableTable>

			<h2 className="text-lg font-semibold">レース履歴</h2>

			{horse.race_histories.length === 0 ? (
				<p className="text-muted-foreground">レース履歴がありません</p>
			) : (
				<ScrollableTable>
					<thead>
						<tr className="border-b bg-muted/50">
							<th
								scope="col"
								className="px-4 py-3 text-left font-medium text-muted-foreground"
							>
								開催日
							</th>
							<th
								scope="col"
								className="px-4 py-3 text-left font-medium text-muted-foreground"
							>
								競馬場
							</th>
							<th
								scope="col"
								className="px-4 py-3 text-left font-medium text-muted-foreground"
							>
								レース番号
							</th>
							<th
								scope="col"
								className="px-4 py-3 text-left font-medium text-muted-foreground"
							>
								レース名
							</th>
							<th
								scope="col"
								className="px-4 py-3 text-left font-medium text-muted-foreground"
							>
								着順
							</th>
							<th
								scope="col"
								className="px-4 py-3 text-left font-medium text-muted-foreground"
							>
								騎手
							</th>
							<th
								scope="col"
								className="px-4 py-3 text-left font-medium text-muted-foreground"
							>
								人気
							</th>
						</tr>
					</thead>
					<tbody>
						{horse.race_histories.map((history) => (
							<tr key={history.race_uid} className="border-b last:border-0">
								<td className="px-4 py-3">
									{formatDateDisplay(history.race_date)}
								</td>
								<td className="px-4 py-3">{history.venue_name}</td>
								<td className="px-4 py-3">{history.race_number}R</td>
								<td className="px-4 py-3">
									{history.race_name ?? "—"}
								</td>
								<td className="px-4 py-3">{history.finishing_order}着</td>
								<td className="px-4 py-3">{history.jockey_name}</td>
								<td className="px-4 py-3">{history.popularity}番人気</td>
							</tr>
						))}
					</tbody>
				</ScrollableTable>
			)}
		</div>
	);
};

export default HorseDetail;

export type { HorseDetailItem, HorseDetailProps } from "./types";
