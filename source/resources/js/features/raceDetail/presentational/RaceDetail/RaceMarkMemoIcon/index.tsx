import { Plus, StickyNote } from "lucide-react";
import type { RaceMarkMemoIconProps } from "./types";

const RaceMarkMemoIcon = ({
	state,
	ariaLabel,
	onClick,
}: RaceMarkMemoIconProps) => {
	return (
		<button
			type="button"
			onClick={onClick}
			aria-label={ariaLabel}
			className="relative inline-flex h-8 w-8 items-center justify-center rounded p-1 hover:bg-muted"
		>
			{state === "edit" ? (
				<>
					<StickyNote className="h-4 w-4 text-primary" />
					<span
						aria-hidden="true"
						className="absolute right-1 top-1 inline-block h-1.5 w-1.5 rounded-full bg-primary"
					/>
				</>
			) : (
				<Plus className="h-4 w-4 text-muted-foreground/60" />
			)}
		</button>
	);
};

export default RaceMarkMemoIcon;

export type { RaceMarkMemoIconProps, RaceMarkMemoIconState } from "./types";
