export type HorseNoteDeleteConfirmDialogProps = {
	open: boolean;
	noteContentPreview: string;
	submitting: boolean;
	errorMessage: string | null;
	onOpenChange: (open: boolean) => void;
	onConfirm: () => void;
};
