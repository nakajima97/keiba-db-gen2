import { Button } from "@/components/shadcn/ui/button";
import AlertError from "@/components/presentational/AlertError";
import type { RaceResultFormProps } from "./types";

export default function RaceResultForm({
	venueName,
	raceDate,
	raceNumber,
	pasteValue,
	onPasteChange,
	parseError,
	onSubmit,
	isSubmitting,
}: RaceResultFormProps) {
	return (
		<div className="flex flex-col gap-6 p-4">
			<div>
				<h1 className="text-xl font-semibold">レース結果入力</h1>
				<p className="text-sm text-muted-foreground">
					{raceDate.replace(/-/g, "/")} {venueName} {raceNumber}R
				</p>
			</div>

			<div className="flex flex-col gap-2">
				<label className="text-sm font-medium">払い戻し情報をペースト</label>
				<textarea
					className="min-h-[200px] w-full rounded-md border border-input bg-background px-3 py-2 text-sm placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring"
					placeholder="JRA公式サイトの払い戻し情報をコピー＆ペーストしてください（単勝・複勝・枠連・ワイド・馬連・馬単・3連複・3連単の全券種が必要です）"
					value={pasteValue}
					onChange={(e) => onPasteChange(e.target.value)}
				/>
			</div>

			{parseError && (
				<AlertError
					errors={[parseError]}
					title="データ形式が正しくありません"
				/>
			)}

			<Button
				onClick={onSubmit}
				disabled={pasteValue.trim() === "" || isSubmitting}
			>
				{isSubmitting ? "保存中..." : "保存する"}
			</Button>
		</div>
	);
}

export type { RaceResultFormProps } from "./types";
