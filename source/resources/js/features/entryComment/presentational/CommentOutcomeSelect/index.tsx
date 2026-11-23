import {
	Select,
	SelectContent,
	SelectItem,
	SelectTrigger,
	SelectValue,
} from "@/components/shadcn/ui/select";
import type { CommentOutcome } from "../../types";
import { OUTCOME_LABELS, OUTCOMES } from "../../utils/labels";
import type { CommentOutcomeSelectProps } from "./types";

/** Radix Select は空文字を値にできないため、未入力をこの値で表す */
const UNSET_VALUE = "unset";

/** 当否（当たり / 外れ / 判定不能 / 未入力）のセレクト */
const CommentOutcomeSelect = ({
	value,
	ariaLabel,
	disabled = false,
	onChange,
}: CommentOutcomeSelectProps) => {
	return (
		<Select
			value={value ?? UNSET_VALUE}
			onValueChange={(next) =>
				onChange(next === UNSET_VALUE ? null : (next as CommentOutcome))
			}
			disabled={disabled}
		>
			<SelectTrigger aria-label={ariaLabel} className="h-8 w-28 text-xs">
				<SelectValue />
			</SelectTrigger>
			<SelectContent>
				<SelectItem value={UNSET_VALUE}>未入力</SelectItem>
				{OUTCOMES.map((outcome) => (
					<SelectItem key={outcome} value={outcome}>
						{OUTCOME_LABELS[outcome]}
					</SelectItem>
				))}
			</SelectContent>
		</Select>
	);
};

export default CommentOutcomeSelect;

export type { CommentOutcomeSelectProps } from "./types";
