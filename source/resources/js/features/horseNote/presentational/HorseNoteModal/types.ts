export type HorseNoteModalMode = "create" | "edit";

export type HorseNoteRaceOption = {
	id: number;
	uid: string;
	label: string;
};

export type HorseNoteModalProps = {
	open: boolean;
	mode: HorseNoteModalMode;
	horseName: string;
	content: string;
	contentMaxLength: number;
	errorMessage: string | null;
	submitting: boolean;
	raceContext:
		| { type: "fixed"; label: string }
		| { type: "selectable"; options: HorseNoteRaceOption[]; selectedUid: string | null }
		| { type: "none" };
	onContentChange: (value: string) => void;
	onRaceSelect?: (uid: string | null) => void;
	onOpenChange: (open: boolean) => void;
	onSubmit: () => void;
};
