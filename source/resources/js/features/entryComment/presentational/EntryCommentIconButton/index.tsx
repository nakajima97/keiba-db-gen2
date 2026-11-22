import HorseNoteIconButton from "@/features/horseNote/presentational/HorseNoteIconButton";
import type { EntryCommentIconButtonProps } from "./types";

const EntryCommentIconButton = ({
	commentCount,
	ariaLabel,
	onClick,
}: EntryCommentIconButtonProps) => {
	return (
		<span className="inline-flex items-center gap-0.5">
			<HorseNoteIconButton
				hasNote={commentCount > 0}
				ariaLabel={ariaLabel}
				onClick={onClick}
			/>
			{commentCount > 0 && (
				<span
					className="rounded-full bg-primary px-1.5 text-xs leading-5 text-primary-foreground"
					aria-label={`${commentCount}件`}
				>
					{commentCount}
				</span>
			)}
		</span>
	);
};

export default EntryCommentIconButton;

export type { EntryCommentIconButtonProps } from "./types";
