import { Link } from "@inertiajs/react";
import { Plus } from "lucide-react";
import { Button } from "@/components/shadcn/ui/button";
import ScrollableTable from "@/components/presentational/ScrollableTable";
import HorseNoteIconButton from "@/features/horseNote/presentational/HorseNoteIconButton";
import { formatDateDisplay } from "@/utils/date";
import RaceMarkSelect from "./RaceMarkSelect";
import RaceMarkColumnHeader from "./RaceMarkColumnHeader";
import type {
	MarkValue,
	RaceDetailProps,
	RaceMarkColumn,
	RaceMarkValue,
} from "./types";

const findMarkValue = (
	marks: RaceMarkValue[],
	columnId: number,
	raceEntryId: number,
): MarkValue | null => {
	const found = marks.find(
		(m) => m.column_id === columnId && m.race_entry_id === raceEntryId,
	);
	return found ? found.mark_value : null;
};

const sortColumns = (columns: RaceMarkColumn[]): RaceMarkColumn[] => {
	return [...columns].sort((a, b) => a.display_order - b.display_order);
};

const RaceDetail = ({
	race,
	onMarkChange,
	onAddOtherColumn,
	onRemoveOtherColumn,
	onChangeColumnLabel,
	onNoteClick,
}: RaceDetailProps) => {
	const sortedColumns = sortColumns(race.mark_columns);

	return (
		<div className="flex flex-col gap-4 p-4">
			<h1 className="text-xl font-semibold">レース詳細</h1>

			<ScrollableTable>
				<tbody>
					<tr className="border-b">
						<th className="w-32 bg-muted/50 px-4 py-3 text-left font-medium text-muted-foreground">
							開催日
						</th>
						<td className="px-4 py-3">{formatDateDisplay(race.race_date)}</td>
					</tr>
					<tr className="border-b">
						<th className="bg-muted/50 px-4 py-3 text-left font-medium text-muted-foreground">
							競馬場
						</th>
						<td className="px-4 py-3">{race.venue_name}</td>
					</tr>
					<tr className="border-b">
						<th className="bg-muted/50 px-4 py-3 text-left font-medium text-muted-foreground">
							レース番号
						</th>
						<td className="px-4 py-3">{race.race_number}R</td>
					</tr>
					<tr>
						<th className="bg-muted/50 px-4 py-3 text-left font-medium text-muted-foreground">
							レース名
						</th>
						<td className="px-4 py-3">{race.race_name ?? "—"}</td>
					</tr>
				</tbody>
			</ScrollableTable>

			<div className="flex items-center justify-between">
				<h2 className="text-lg font-semibold">出馬表</h2>
				<div className="flex items-center gap-2">
					<Button
						type="button"
						variant="outline"
						size="sm"
						onClick={onAddOtherColumn}
					>
						<Plus className="mr-1 h-4 w-4" />
						他人の印を追加
					</Button>
					<Button asChild variant="outline" size="sm">
						<Link href={`/races/${race.uid}/entries/new`}>出走馬登録</Link>
					</Button>
				</div>
			</div>

			<ScrollableTable>
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
						{sortedColumns.map((column) => (
							<th
								key={column.id}
								className="px-4 py-3 text-left font-medium text-muted-foreground"
							>
								<RaceMarkColumnHeader
									column={column}
									onChangeLabel={(label) =>
										onChangeColumnLabel(column.id, label)
									}
									onRemove={() => onRemoveOtherColumn(column.id)}
								/>
							</th>
						))}
					</tr>
				</thead>
				<tbody>
					{race.entries.map((entry) => (
						<tr key={entry.horse_number} className="border-b last:border-0">
							<td className="px-4 py-3">{entry.frame_number}</td>
							<td className="px-4 py-3">{entry.horse_number}</td>
							<td className="px-4 py-3">
								<div className="flex items-center gap-1">
									<Link
										href={`/horses/${entry.horse_id}`}
										className="text-primary hover:underline"
									>
										{entry.horse_name}
									</Link>
									<HorseNoteIconButton
										hasNote={entry.note != null}
										ariaLabel={`${entry.horse_name}のメモ`}
										onClick={() => onNoteClick?.(entry.horse_id)}
									/>
								</div>
							</td>
							<td className="px-4 py-3">{entry.jockey_name}</td>
							<td className="px-4 py-3">
								{entry.weight !== null ? `${entry.weight}kg` : "-"}
							</td>
							{sortedColumns.map((column) => (
								<td key={column.id} className="px-4 py-3">
									<RaceMarkSelect
										value={findMarkValue(race.marks, column.id, entry.id)}
										ariaLabel={`${entry.horse_name}の印（${
											column.type === "own" ? "自分" : (column.label ?? "他人")
										}）`}
										onChange={(markValue) =>
											onMarkChange({
												columnId: column.id,
												raceEntryId: entry.id,
												markValue,
											})
										}
									/>
								</td>
							))}
						</tr>
					))}
				</tbody>
			</ScrollableTable>
		</div>
	);
};

export default RaceDetail;

export type {
	RaceDetailItem,
	RaceDetailProps,
	MarkValue,
	RaceMarkColumn,
	RaceMarkValue,
} from "./types";
