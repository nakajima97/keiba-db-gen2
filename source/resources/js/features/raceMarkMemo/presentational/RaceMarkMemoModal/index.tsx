import AlertError from "@/components/presentational/AlertError";
import { Button } from "@/components/shadcn/ui/button";
import {
	Dialog,
	DialogClose,
	DialogContent,
	DialogDescription,
	DialogFooter,
	DialogHeader,
	DialogTitle,
} from "@/components/shadcn/ui/dialog";
import { useRef } from "react";
import type { RaceMarkMemoModalProps } from "./types";

const RaceMarkMemoModal = ({
	open,
	mode,
	horseName,
	columnLabel,
	markValue,
	content,
	contentMaxLength,
	errorMessage,
	submitting,
	onContentChange,
	onOpenChange,
	onSubmit,
	onDelete,
}: RaceMarkMemoModalProps) => {
	const textareaRef = useRef<HTMLTextAreaElement>(null);
	const isOverLimit = content.length > contentMaxLength;
	const isEmpty = content.trim() === "";
	const title = mode === "create" ? "印メモを追加" : "印メモを編集";
	const columnLabelDisplay = columnLabel === "" ? "（ラベル未設定）" : columnLabel;
	const markValueDisplay = markValue ?? "―";

	return (
		<Dialog open={open} onOpenChange={onOpenChange}>
			<DialogContent
				className="sm:max-w-lg"
				onOpenAutoFocus={(event) => {
					if (mode === "edit" && textareaRef.current) {
						event.preventDefault();
						const textarea = textareaRef.current;
						const length = textarea.value.length;
						textarea.focus();
						textarea.setSelectionRange(length, length);
					}
				}}
			>
				<DialogHeader>
					<DialogTitle>{title}</DialogTitle>
					<DialogDescription>{horseName}</DialogDescription>
				</DialogHeader>

				<div className="flex flex-col gap-4">
					<div className="flex items-center gap-4 rounded-md bg-muted px-3 py-2 text-sm">
						<div className="flex flex-col">
							<span className="text-xs text-muted-foreground">予想者</span>
							<span className="font-medium">{columnLabelDisplay}</span>
						</div>
						<div className="flex flex-col">
							<span className="text-xs text-muted-foreground">印</span>
							<span className="font-medium">{markValueDisplay}</span>
						</div>
					</div>

					<div className="flex flex-col gap-1">
						<label
							htmlFor="race-mark-memo-content"
							className="text-sm font-medium"
						>
							メモ
						</label>
						<textarea
							ref={textareaRef}
							id="race-mark-memo-content"
							className="min-h-[160px] w-full rounded-md border border-input bg-background px-3 py-2 text-sm placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:text-muted-foreground"
							placeholder="この印を打った人の根拠（展開・馬場・調子など）を記入"
							value={content}
							onChange={(e) => onContentChange(e.target.value)}
							disabled={submitting}
						/>
						<div
							className={`text-xs ${isOverLimit ? "text-destructive" : "text-muted-foreground"} self-end`}
						>
							{content.length} / {contentMaxLength}
						</div>
					</div>

					{errorMessage !== null && (
						<AlertError errors={[errorMessage]} title="エラー" />
					)}
				</div>

				<DialogFooter className="flex-row justify-between sm:justify-between">
					<div>
						{mode === "edit" && onDelete !== undefined && (
							<Button
								variant="destructive"
								onClick={onDelete}
								disabled={submitting}
							>
								削除
							</Button>
						)}
					</div>
					<div className="flex gap-2">
						<DialogClose asChild>
							<Button variant="outline" disabled={submitting}>
								キャンセル
							</Button>
						</DialogClose>
						<Button
							onClick={onSubmit}
							disabled={submitting || isEmpty || isOverLimit}
						>
							{mode === "create" ? "追加" : "保存"}
						</Button>
					</div>
				</DialogFooter>
			</DialogContent>
		</Dialog>
	);
};

export default RaceMarkMemoModal;

export type { RaceMarkMemoModalMode, RaceMarkMemoModalProps } from "./types";
