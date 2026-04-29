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
import type { HorseNoteModalProps } from "./types";

const HorseNoteModal = ({
	open,
	mode,
	horseName,
	content,
	contentMaxLength,
	errorMessage,
	submitting,
	raceContext,
	onContentChange,
	onRaceSelect,
	onOpenChange,
	onSubmit,
}: HorseNoteModalProps) => {
	const textareaRef = useRef<HTMLTextAreaElement>(null);
	const isOverLimit = content.length > contentMaxLength;
	const isEmpty = content.trim() === "";
	const title = mode === "create" ? "メモを追加" : "メモを編集";

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
					{raceContext.type === "fixed" && (
						<div className="flex flex-col gap-1">
							<span className="text-sm font-medium">紐づくレース</span>
							<span className="text-sm text-muted-foreground">
								{raceContext.label}
							</span>
						</div>
					)}

					{raceContext.type === "selectable" && (
						<div className="flex flex-col gap-1">
							<label
								htmlFor="horse-note-race-select"
								className="text-sm font-medium"
							>
								紐づくレース（任意）
							</label>
							<select
								id="horse-note-race-select"
								className="w-full rounded-md border border-input bg-background px-3 py-2 text-sm focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring"
								value={raceContext.selectedUid ?? ""}
								onChange={(e) =>
									onRaceSelect?.(e.target.value === "" ? null : e.target.value)
								}
							>
								<option value="">レースに紐づけない</option>
								{raceContext.options.map((option) => (
									<option key={option.uid} value={option.uid}>
										{option.label}
									</option>
								))}
							</select>
						</div>
					)}

					<div className="flex flex-col gap-1">
						<label htmlFor="horse-note-content" className="text-sm font-medium">
							メモ
						</label>
						<textarea
							ref={textareaRef}
							id="horse-note-content"
							className="min-h-[160px] w-full rounded-md border border-input bg-background px-3 py-2 text-sm placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:text-muted-foreground"
							placeholder="次走への備忘録、調子の所感などを記入"
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

				<DialogFooter>
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
				</DialogFooter>
			</DialogContent>
		</Dialog>
	);
};

export default HorseNoteModal;

export type {
	HorseNoteModalMode,
	HorseNoteModalProps,
	HorseNoteRaceOption,
} from "./types";
