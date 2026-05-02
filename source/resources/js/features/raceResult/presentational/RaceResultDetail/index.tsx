import { Link } from "@inertiajs/react";
import BackButton from "@/components/presentational/BackButton";
import ScrollableTable from "@/components/presentational/ScrollableTable";
import { Button } from "@/components/shadcn/ui/button";
import HorseNoteCell from "@/features/horseNote/presentational/HorseNoteCell";
import { index as ticketsIndex } from "@/routes/tickets";
import { formatDateDisplay } from "@/utils/date";
import type { RaceResultDetailProps } from "./types";
import { formatHorseNumbers } from "./utils";

const RaceResultDetail = ({ race, onNoteClick, onDeleteClick }: RaceResultDetailProps) => {
	return (
		<div className="flex flex-col gap-4 p-4">
			<div>
				<BackButton label="購入馬券一覧へ戻る" href={ticketsIndex.url()} />
			</div>
			<div className="flex items-center justify-between">
				<div>
					<h1 className="text-xl font-semibold">レース結果</h1>
					<p className="text-sm text-muted-foreground">
						{formatDateDisplay(race.race_date)} {race.venue_name}{" "}
						{race.race_number}R
					</p>
				</div>
				<div className="flex items-center gap-2">
					{race.finishing_horses.length === 0 && (
						<Button asChild variant="outline" size="sm">
							<Link href={`/races/${race.uid}/result/new`}>
								レース結果入力
							</Link>
						</Button>
					)}
					{race.finishing_horses.length > 0 && onDeleteClick !== undefined && (
						<Button
							variant="destructive"
							size="sm"
							onClick={onDeleteClick}
						>
							レース結果を削除
						</Button>
					)}
				</div>
			</div>

			<div className="flex flex-col gap-2">
				<h2 className="text-base font-semibold">着順</h2>
				{race.finishing_horses.length === 0 ? (
					<p className="text-sm text-muted-foreground">
						着順データがありません
					</p>
				) : (
					<ScrollableTable>
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
								<th className="px-4 py-3 text-left font-medium text-muted-foreground">
									メモ
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
										{horse.horse_id !== null ? (
											<Link
												href={`/horses/${horse.horse_id}`}
												className="text-primary hover:underline"
											>
												{horse.horse_name}
											</Link>
										) : (
											horse.horse_name
										)}
									</td>
									<td className="px-4 py-3">
										{horse.jockey_name}
									</td>
									<td className="px-4 py-3 text-right">
										{horse.race_time}
									</td>
									<td className="px-4 py-3">
										{horse.horse_id !== null ? (
											<HorseNoteCell
												content={horse.note?.content ?? null}
												source={horse.note?.source ?? null}
												onClick={() => {
													if (horse.horse_id !== null) {
														onNoteClick?.(horse.horse_id);
													}
												}}
											/>
										) : (
											<span className="text-sm text-muted-foreground">—</span>
										)}
									</td>
								</tr>
							))}
						</tbody>
					</ScrollableTable>
				)}
			</div>

			<ScrollableTable>
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
			</ScrollableTable>
		</div>
	);
};

export default RaceResultDetail;

export type { RaceResultDetailProps } from "./types";
