import { Plus, StickyNote } from "lucide-react";
import type { HorseNoteIconButtonProps } from "./types";

const HorseNoteIconButton = ({
	hasNote,
	ariaLabel,
	onClick,
}: HorseNoteIconButtonProps) => {
	return (
		<button
			type="button"
			onClick={onClick}
			aria-label={ariaLabel}
			className="inline-flex items-center justify-center rounded p-1 hover:bg-muted"
		>
			{hasNote ? (
				<StickyNote className="h-4 w-4 text-primary" />
			) : (
				<Plus className="h-4 w-4 text-muted-foreground/60" />
			)}
		</button>
	);
};

export default HorseNoteIconButton;

export type { HorseNoteIconButtonProps } from "./types";
