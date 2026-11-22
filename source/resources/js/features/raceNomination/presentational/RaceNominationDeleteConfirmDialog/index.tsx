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
import type { RaceNominationDeleteConfirmDialogProps } from "./types";

const RaceNominationDeleteConfirmDialog = ({
	open,
	horseName,
	submitting,
	onOpenChange,
	onConfirm,
}: RaceNominationDeleteConfirmDialogProps) => {
	return (
		<Dialog open={open} onOpenChange={onOpenChange}>
			<DialogContent className="sm:max-w-md">
				<DialogHeader>
					<DialogTitle>登録馬を削除</DialogTitle>
					<DialogDescription>
						{horseName}
						を登録馬から削除します。チェック／消しも削除されます（メモは残ります）。
					</DialogDescription>
				</DialogHeader>
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

export default RaceNominationDeleteConfirmDialog;

export type { RaceNominationDeleteConfirmDialogProps } from "./types";
