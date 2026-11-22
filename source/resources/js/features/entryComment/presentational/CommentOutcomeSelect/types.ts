import type { CommentOutcome } from "../../types";

export type CommentOutcomeSelectProps = {
	value: CommentOutcome | null;
	ariaLabel: string;
	disabled?: boolean;
	onChange: (value: CommentOutcome | null) => void;
};
