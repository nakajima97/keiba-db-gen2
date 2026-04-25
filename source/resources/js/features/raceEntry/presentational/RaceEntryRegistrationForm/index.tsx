import { Button } from "@/components/shadcn/ui/button";
import { formatDateDisplay } from "@/utils/date";
import type { RaceEntryRegistrationFormProps } from "./types";

export type { RaceEntryRegistrationFormProps } from "./types";

export default function RaceEntryRegistrationForm({
	raceInfo,
	pastedText,
	isSubmitting,
	onPastedTextChange,
	onSubmit,
}: RaceEntryRegistrationFormProps) {
	return (
		<div className="mx-auto max-w-2xl space-y-8 p-4">
			<h1 className="text-xl font-semibold">出走馬登録</h1>

			<div className="overflow-x-auto rounded-xl border">
				<table className="w-full text-sm">
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
				</table>
			</div>

			<div className="space-y-2">
				<label
					htmlFor="jra-paste"
					className="text-sm font-medium text-foreground"
				>
					JRA出馬表テキスト
				</label>
				<p className="text-sm text-muted-foreground">
					JRAの出馬表をコピーしてペーストしてください。
				</p>
				<textarea
					id="jra-paste"
					className="min-h-[320px] w-full rounded-md border border-input bg-background px-3 py-2 text-sm placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:opacity-50"
					value={pastedText}
					placeholder="ここにJRAの出馬表テキストをペーストしてください"
					disabled={isSubmitting}
					onChange={(e) => onPastedTextChange(e.target.value)}
				/>
			</div>

			<div className="flex gap-3 pt-2">
				<Button
					type="submit"
					className="flex-1"
					disabled={isSubmitting || pastedText.trim() === ""}
					onClick={onSubmit}
				>
					{isSubmitting ? "登録中..." : "登録"}
				</Button>
			</div>
		</div>
	);
}
