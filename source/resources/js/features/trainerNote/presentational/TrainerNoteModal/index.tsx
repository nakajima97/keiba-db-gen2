import { useRef } from "react";
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
import type { TrainerNoteModalProps } from "./types";

const TrainerNoteModal = ({
	open,
	mode,
	trainerName,
	trainerAffiliation,
	content,
	contentMaxLength,
	errorMessage,
	submitting,
	onContentChange,
	onOpenChange,
	onSubmit,
	onDelete,
}: TrainerNoteModalProps) => {
	const textareaRef = useRef<HTMLTextAreaElement>(null);
	const isOverLimit = content.length > contentMaxLength;
	const isEmpty = content.trim() === "";
	const title = mode === "create" ? "調教師メモを追加" : "調教師メモを編集";
	const trainerLabel =
		trainerAffiliation != null
			? `${trainerName}（${trainerAffiliation}）`
			: trainerName;

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
					<DialogDescription>{trainerLabel}</DialogDescription>
				</DialogHeader>

				<div className="flex flex-col gap-4">
					<div className="flex flex-col gap-1">
						<label htmlFor="trainer-note-content" className="text-sm font-medium">
							メモ
						</label>
						<textarea
							ref={textareaRef}
							id="trainer-note-content"
							className="min-h-[160px] w-full rounded-md border border-input bg-background px-3 py-2 text-sm placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:text-muted-foreground"
							placeholder="調教師コメントをどこまで信用してよいか（強気コメントでも凡走が多い、など）を記入"
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

export default TrainerNoteModal;

export type { TrainerNoteModalMode, TrainerNoteModalProps } from "./types";
