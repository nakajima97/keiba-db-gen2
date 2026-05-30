import type { ChangeEvent, FormEvent } from "react";
import BackButton from "@/components/presentational/BackButton";
import ScrollableTable from "@/components/presentational/ScrollableTable";
import { Button } from "@/components/shadcn/ui/button";
import { Input } from "@/components/shadcn/ui/input";
import { show as raceShow } from "@/routes/races";
import { formatDateDisplay } from "@/utils/date";
import type {
	RaceEntryEditFormProps,
	RaceEntryEditFormValues,
} from "./types";

export type {
	RaceEntryEditFormProps,
	RaceEntryEditFormValues,
	RaceEntryEditFormErrors,
	RaceInfo,
} from "./types";

const RaceEntryEditForm = ({
	raceUid,
	raceInfo,
	values,
	errors,
	isSubmitting,
	onChange,
	onSubmit,
	headingLabel = "出走馬編集",
	submitLabel = "更新",
}: RaceEntryEditFormProps) => {
	const handleSubmit = (e: FormEvent<HTMLFormElement>) => {
		e.preventDefault();
		onSubmit();
	};

	const handleChange =
		(field: keyof RaceEntryEditFormValues) =>
		(e: ChangeEvent<HTMLInputElement>) => {
			onChange(field, e.target.value);
		};

	return (
		<div className="space-y-8">
			<div>
				<BackButton
					label="レース詳細へ戻る"
					href={raceShow.url({ race: raceUid })}
				/>
			</div>

			<h1 className="text-xl font-semibold">{headingLabel}</h1>

			<ScrollableTable>
				<tbody>
					<tr className="border-b">
						<th className="w-32 bg-muted/50 px-4 py-3 text-left font-medium text-muted-foreground">
							開催日
						</th>
						<td className="px-4 py-3">{formatDateDisplay(raceInfo.race_date)}</td>
					</tr>
					<tr className="border-b">
						<th className="bg-muted/50 px-4 py-3 text-left font-medium text-muted-foreground">
							競馬場
						</th>
						<td className="px-4 py-3">{raceInfo.venue_name}</td>
					</tr>
					<tr>
						<th className="bg-muted/50 px-4 py-3 text-left font-medium text-muted-foreground">
							レース番号
						</th>
						<td className="px-4 py-3">{raceInfo.race_number}R</td>
					</tr>
				</tbody>
			</ScrollableTable>

			<form onSubmit={handleSubmit} className="space-y-6">
				<div className="space-y-2">
					<label
						htmlFor="horse_name"
						className="text-sm font-medium text-foreground"
					>
						馬名
					</label>
					<Input
						id="horse_name"
						type="text"
						value={values.horse_name}
						disabled={isSubmitting}
						aria-invalid={errors.horse_name !== undefined}
						onChange={handleChange("horse_name")}
					/>
					{errors.horse_name !== undefined && (
						<p className="text-sm text-destructive">{errors.horse_name}</p>
					)}
				</div>

				<div className="space-y-2">
					<label
						htmlFor="jockey_name"
						className="text-sm font-medium text-foreground"
					>
						騎手名
					</label>
					<Input
						id="jockey_name"
						type="text"
						value={values.jockey_name}
						disabled={isSubmitting}
						aria-invalid={errors.jockey_name !== undefined}
						onChange={handleChange("jockey_name")}
					/>
					{errors.jockey_name !== undefined && (
						<p className="text-sm text-destructive">{errors.jockey_name}</p>
					)}
				</div>

				<div className="grid grid-cols-1 gap-6 sm:grid-cols-2">
					<div className="space-y-2">
						<label
							htmlFor="frame_number"
							className="text-sm font-medium text-foreground"
						>
							枠番（1〜8）
						</label>
						<Input
							id="frame_number"
							type="number"
							min={1}
							max={8}
							value={values.frame_number}
							disabled={isSubmitting}
							aria-invalid={errors.frame_number !== undefined}
							onChange={handleChange("frame_number")}
						/>
						{errors.frame_number !== undefined && (
							<p className="text-sm text-destructive">{errors.frame_number}</p>
						)}
					</div>

					<div className="space-y-2">
						<label
							htmlFor="horse_number"
							className="text-sm font-medium text-foreground"
						>
							馬番（1〜18）
						</label>
						<Input
							id="horse_number"
							type="number"
							min={1}
							max={18}
							value={values.horse_number}
							disabled={isSubmitting}
							aria-invalid={errors.horse_number !== undefined}
							onChange={handleChange("horse_number")}
						/>
						{errors.horse_number !== undefined && (
							<p className="text-sm text-destructive">{errors.horse_number}</p>
						)}
					</div>
				</div>

				<div className="grid grid-cols-1 gap-6 sm:grid-cols-2">
					<div className="space-y-2">
						<label
							htmlFor="weight"
							className="text-sm font-medium text-foreground"
						>
							負担重量（kg）
						</label>
						<Input
							id="weight"
							type="number"
							step="0.1"
							value={values.weight}
							disabled={isSubmitting}
							aria-invalid={errors.weight !== undefined}
							onChange={handleChange("weight")}
						/>
						{errors.weight !== undefined && (
							<p className="text-sm text-destructive">{errors.weight}</p>
						)}
					</div>

					<div className="space-y-2">
						<label
							htmlFor="horse_weight"
							className="text-sm font-medium text-foreground"
						>
							馬体重（kg、任意）
						</label>
						<Input
							id="horse_weight"
							type="number"
							value={values.horse_weight}
							disabled={isSubmitting}
							aria-invalid={errors.horse_weight !== undefined}
							onChange={handleChange("horse_weight")}
						/>
						{errors.horse_weight !== undefined && (
							<p className="text-sm text-destructive">{errors.horse_weight}</p>
						)}
					</div>
				</div>

				<div className="flex gap-3 pt-2">
					<Button type="submit" className="flex-1" disabled={isSubmitting}>
						{isSubmitting ? `${submitLabel}中...` : submitLabel}
					</Button>
				</div>
			</form>
		</div>
	);
};

export default RaceEntryEditForm;
