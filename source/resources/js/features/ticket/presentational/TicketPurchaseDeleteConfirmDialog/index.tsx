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
import { formatDateDisplay } from "@/utils/date";
import type { TicketPurchaseDeleteConfirmDialogProps } from "./types";

const TicketPurchaseDeleteConfirmDialog = ({
	open,
	ticketSummary,
	submitting,
	errorMessage,
	onOpenChange,
	onConfirm,
}: TicketPurchaseDeleteConfirmDialogProps) => {
	const raceDateText =
		ticketSummary?.raceDate != null
			? formatDateDisplay(ticketSummary.raceDate)
			: "-";
	const venueText = ticketSummary?.venueName ?? "-";
	const raceNumberText =
		ticketSummary?.raceNumber != null ? `${ticketSummary.raceNumber}R` : "-";
	const ticketTypeText = ticketSummary?.ticketTypeLabel ?? "-";

	return (
		<Dialog open={open} onOpenChange={onOpenChange}>
			<DialogContent className="sm:max-w-md">
				<DialogHeader>
					<DialogTitle>馬券を削除</DialogTitle>
					<DialogDescription>
						この馬券を削除します。この操作は取り消せません。
					</DialogDescription>
				</DialogHeader>

				<div className="flex flex-col gap-4">
					{ticketSummary !== null && (
						<div className="rounded-md border bg-muted/40 p-3">
							<p className="break-words text-sm text-muted-foreground">
								{`${raceDateText} / ${venueText} / ${raceNumberText} / ${ticketTypeText}`}
							</p>
						</div>
					)}

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

export default TicketPurchaseDeleteConfirmDialog;

export type { TicketPurchaseDeleteConfirmDialogProps } from "./types";
