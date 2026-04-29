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
import type { HorseNoteDeleteConfirmDialogProps } from "./types";

const HorseNoteDeleteConfirmDialog = ({
	open,
	noteContentPreview,
	submitting,
	errorMessage,
	onOpenChange,
	onConfirm,
}: HorseNoteDeleteConfirmDialogProps) => {
	return (
		<Dialog open={open} onOpenChange={onOpenChange}>
			<DialogContent className="sm:max-w-md">
				<DialogHeader>
					<DialogTitle>メモを削除</DialogTitle>
					<DialogDescription>
						このメモを削除します。この操作は取り消せません。
					</DialogDescription>
				</DialogHeader>

				<div className="flex flex-col gap-4">
					<div className="rounded-md border bg-muted/40 p-3">
						<p className="whitespace-pre-wrap break-words text-sm text-muted-foreground">
							{noteContentPreview}
						</p>
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
						variant="destructive"
						onClick={onConfirm}
						disabled={submitting}
					>
						削除する
					</Button>
				</DialogFooter>
			</DialogContent>
		</Dialog>
	);
};

export default HorseNoteDeleteConfirmDialog;

export type { HorseNoteDeleteConfirmDialogProps } from "./types";
