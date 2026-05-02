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

type DeleteResultModalProps = {
	open: boolean;
	isLoading: boolean;
	errorMessage: string | null;
	onConfirm: () => void;
	onCancel: () => void;
};

const DeleteResultModal = ({
	open,
	isLoading,
	errorMessage,
	onConfirm,
	onCancel,
}: DeleteResultModalProps) => {
	return (
		<Dialog open={open} onOpenChange={(isOpen) => { if (!isOpen) { onCancel(); } }}>
			<DialogContent className="sm:max-w-md">
				<DialogHeader>
					<DialogTitle>レース結果を削除</DialogTitle>
					<DialogDescription>
						このレースの着順・払戻データをすべて削除します。この操作は取り消せません。
					</DialogDescription>
				</DialogHeader>

				{errorMessage !== null && (
					<AlertError errors={[errorMessage]} title="エラー" />
				)}

				<DialogFooter>
					<DialogClose asChild>
						<Button variant="outline" disabled={isLoading}>
							キャンセル
						</Button>
					</DialogClose>
					<Button
						variant="destructive"
						onClick={onConfirm}
						disabled={isLoading}
					>
						{isLoading ? "削除中..." : "削除する"}
					</Button>
				</DialogFooter>
			</DialogContent>
		</Dialog>
	);
};

export default DeleteResultModal;

export type { DeleteResultModalProps };
