export type TrainerNoteModalMode = "create" | "edit";

export type TrainerNoteModalProps = {
	open: boolean;
	mode: TrainerNoteModalMode;
	trainerName: string;
	trainerAffiliation: string | null;
	content: string;
	contentMaxLength: number;
	errorMessage: string | null;
	submitting: boolean;
	onContentChange: (value: string) => void;
	onOpenChange: (open: boolean) => void;
	onSubmit: () => void;
	onDelete?: () => void;
};
