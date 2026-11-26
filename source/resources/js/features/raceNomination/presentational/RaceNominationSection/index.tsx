import { Link } from "@inertiajs/react";
import { Plus, Trash2 } from "lucide-react";
import ScrollableTable from "@/components/presentational/ScrollableTable";
import { Button } from "@/components/shadcn/ui/button";
import HorseNoteIconButton from "@/features/horseNote/presentational/HorseNoteIconButton";
import RaceMarkColumnHeader from "@/features/raceDetail/presentational/RaceDetail/RaceMarkColumnHeader";
import RaceMarkMemoIcon from "@/features/raceDetail/presentational/RaceDetail/RaceMarkMemoIcon";
import RaceMarkSelect from "@/features/raceDetail/presentational/RaceDetail/RaceMarkSelect";
import RaceNominationStatusToggle from "../RaceNominationStatusToggle";
import type { RaceNominationSectionProps } from "./types";

const HEADER_CELL_CLASS =
	"bg-[color-mix(in_srgb,var(--color-muted)_50%,var(--color-background))] px-4 py-3 text-left font-medium text-muted-foreground";

const RaceNominationSection = ({
	nominations,
	addForm,
	markColumns,
	marks,
	markMemos,
	onStatusChange,
	onNoteClick,
	onDeleteClick,
	onAddOtherColumn,
	onChangeColumnLabel,
	onRemoveOtherColumn,
	onMarkChange,
	onMarkMemoClick,
}: RaceNominationSectionProps) => {
	const otherColumns = [...markColumns].sort(
		(a, b) => a.display_order - b.display_order,
	);

	return (
		<section className="flex flex-col gap-2" aria-labelledby="race-nominations-heading">
			<div className="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
				<h2 id="race-nominations-heading" className="text-lg font-semibold">
					登録馬
				</h2>
				<Button
					type="button"
					variant="outline"
					size="sm"
					onClick={onAddOtherColumn}
				>
					<Plus className="mr-1 h-4 w-4" />
					他人の印を追加
				</Button>
			</div>
			<p className="text-sm text-muted-foreground">
				特別登録・出走登録など、出走確定前の馬を記録できます。
			</p>
			{addForm}

			{nominations.length === 0 ? (
				<p className="text-sm text-muted-foreground">登録馬はまだありません。</p>
			) : (
				<ScrollableTable>
					<thead>
						<tr className="border-b">
							<th className={`sticky left-0 z-10 border-r ${HEADER_CELL_CLASS}`}>
								馬名
							</th>
							<th className={HEADER_CELL_CLASS}>チェック／消し</th>
							{otherColumns.map((column) => (
								<th key={column.id} className={HEADER_CELL_CLASS}>
									<RaceMarkColumnHeader
										column={{ ...column, type: "other" }}
										onChangeLabel={(label) =>
											onChangeColumnLabel(column.id, label)
										}
										onRemove={() => onRemoveOtherColumn(column.id)}
									/>
								</th>
							))}
							<th className={HEADER_CELL_CLASS}>操作</th>
						</tr>
					</thead>
					<tbody>
						{nominations.map((nomination) => (
							<tr key={nomination.id} className="border-b last:border-0">
								<td className="sticky left-0 z-10 border-r bg-background px-4 py-3">
									<div className="flex max-w-[12rem] items-center gap-1 overflow-hidden">
										<Link
											href={`/horses/${nomination.horse_uid}`}
											className="min-w-0 flex-1 truncate text-primary hover:underline"
										>
											{nomination.horse_name}
										</Link>
										<span className="shrink-0">
											<HorseNoteIconButton
												hasNote={nomination.note != null}
												ariaLabel={`${nomination.horse_name}のメモ`}
												onClick={() => onNoteClick(nomination.horse_id)}
											/>
										</span>
									</div>
								</td>
								<td className="px-4 py-3">
									<RaceNominationStatusToggle
										value={nomination.status}
										horseName={nomination.horse_name}
										onChange={(status) => onStatusChange(nomination.id, status)}
									/>
								</td>
								{otherColumns.map((column) => {
									const markValue =
										marks.find(
											(mark) =>
												mark.column_id === column.id &&
												mark.race_nomination_id === nomination.id,
										)?.mark_value ?? null;
									const memoExists = markMemos.some(
										(memo) =>
											memo.column_id === column.id &&
											memo.race_nomination_id === nomination.id,
									);
									const columnLabel = column.label || "他人";
									return (
										<td key={column.id} className="px-4 py-3">
											<div className="flex items-center gap-1">
												<RaceMarkSelect
													value={markValue}
													ariaLabel={`${nomination.horse_name}の印（${columnLabel}）`}
													onChange={(nextMarkValue) =>
														onMarkChange({
															columnId: column.id,
															raceNominationId: nomination.id,
															markValue: nextMarkValue,
														})
													}
												/>
												{(markValue !== null || memoExists) && (
													<RaceMarkMemoIcon
														state={memoExists ? "edit" : "add"}
														ariaLabel={`${nomination.horse_name}の${columnLabel}の印メモ`}
														onClick={() =>
															onMarkMemoClick({
																columnId: column.id,
																raceNominationId: nomination.id,
															})
														}
													/>
												)}
											</div>
										</td>
									);
								})}
								<td className="px-4 py-3">
									<Button
										type="button"
										variant="ghost"
										size="sm"
										aria-label={`${nomination.horse_name}を登録馬から削除`}
										onClick={() => onDeleteClick(nomination.id)}
									>
										<Trash2 className="h-4 w-4" />
									</Button>
								</td>
							</tr>
						))}
					</tbody>
				</ScrollableTable>
			)}
		</section>
	);
};

export default RaceNominationSection;

export type { RaceNominationSectionProps } from "./types";
