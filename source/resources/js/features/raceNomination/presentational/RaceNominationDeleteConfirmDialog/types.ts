export type RaceNominationDeleteConfirmDialogProps = {
	open: boolean;
	horseName: string;
	submitting: boolean;
	onOpenChange: (open: boolean) => void;
	onConfirm: () => void;
};
