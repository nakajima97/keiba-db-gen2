export type TicketPurchaseDeleteConfirmDialogProps = {
	open: boolean;
	ticketSummary: {
		raceDate: string | null;
		venueName: string | null;
		raceNumber: number | null;
		ticketTypeLabel: string;
	} | null;
	submitting: boolean;
	errorMessage: string | null;
	onOpenChange: (open: boolean) => void;
	onConfirm: () => void;
};
