import { Button } from "@/components/shadcn/ui/button";
import AlertError from "@/components/presentational/AlertError";
import { formatDateDisplay } from "@/utils/date";
import type { RaceResultFormProps } from "./types";

const RaceResultForm = ({
	venueName,
	raceDate,
	raceNumber,
	resultPasteValue,
	onResultPasteChange,
	resultParseError,
	payoutPasteValue,
	onPayoutPasteChange,
	payoutParseError,
	onSubmit,
	isSubmitting,
	disabled,
}: RaceResultFormProps) => {
	return (
		<div className="flex flex-col gap-6 p-4">
			<div>
				<h1 className="text-xl font-semibold">レース結果入力</h1>
				<p className="text-sm text-muted-foreground">
					{formatDateDisplay(raceDate)} {venueName} {raceNumber}R
				</p>
			</div>

			<div className="flex flex-col gap-2">
				<label htmlFor="result-paste-value" className="text-sm font-medium">
					着順情報をペースト
				</label>
				<textarea
					id="result-paste-value"
					className="min-h-[200px] w-full rounded-md border border-input bg-background px-3 py-2 text-sm placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:text-muted-foreground"
					placeholder="JRA公式サイトの着順情報をコピー＆ペーストしてください"
					value={resultPasteValue}
					onChange={(e) => onResultPasteChange(e.target.value)}
					disabled={disabled}
				/>
			</div>

			{resultParseError && (
				<AlertError
					errors={[resultParseError]}
					title="データ形式が正しくありません"
				/>
			)}

			<div className="flex flex-col gap-2">
				<label htmlFor="payout-paste-value" className="text-sm font-medium">
					払い戻し情報をペースト
				</label>
				<textarea
					id="payout-paste-value"
					className="min-h-[200px] w-full rounded-md border border-input bg-background px-3 py-2 text-sm placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:text-muted-foreground"
					placeholder="JRA公式サイトの払い戻し情報をコピー＆ペーストしてください（単勝・複勝・枠連・ワイド・馬連・馬単・3連複・3連単の全券種が必要です）"
					value={payoutPasteValue}
					onChange={(e) => onPayoutPasteChange(e.target.value)}
					disabled={disabled}
				/>
			</div>

			{payoutParseError && (
				<AlertError
					errors={[payoutParseError]}
					title="データ形式が正しくありません"
				/>
			)}

			<Button
				onClick={onSubmit}
				disabled={
					resultPasteValue.trim() === "" ||
					payoutPasteValue.trim() === "" ||
					isSubmitting ||
					disabled
				}
			>
				{isSubmitting ? "保存中..." : "保存する"}
			</Button>
		</div>
	);
};

export default RaceResultForm;

export type { RaceResultFormProps } from "./types";
