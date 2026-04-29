export type RaceMarkMemoIconState = "add" | "edit";

export type RaceMarkMemoIconProps = {
	state: RaceMarkMemoIconState;
	ariaLabel: string;
	onClick: () => void;
};
