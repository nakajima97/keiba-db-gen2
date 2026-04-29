import type { HorseNoteCellProps } from "./types";

const HorseNoteCell = ({ content, source, onClick }: HorseNoteCellProps) => {
	if (content === null) {
		return (
			<button
				type="button"
				onClick={onClick}
				className="text-sm text-muted-foreground hover:text-foreground hover:underline"
			>
				+ メモを追加
			</button>
		);
	}

	return (
		<button
			type="button"
			onClick={onClick}
			className="flex max-w-xs flex-col items-start gap-1 text-left hover:underline"
		>
			<span className="line-clamp-2 whitespace-pre-wrap text-sm">
				{content}
			</span>
			{source === "horse" && (
				<span className="text-xs text-muted-foreground">
					（レース紐づきなしのメモ）
				</span>
			)}
		</button>
	);
};

export default HorseNoteCell;

export type { HorseNoteCellProps, HorseNoteCellSource } from "./types";
