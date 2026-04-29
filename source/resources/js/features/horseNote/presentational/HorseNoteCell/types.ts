export type HorseNoteCellSource = "race" | "horse" | null;

export type HorseNoteCellProps = {
	content: string | null;
	source: HorseNoteCellSource;
	onClick: () => void;
};
