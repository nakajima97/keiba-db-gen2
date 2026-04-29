import {
	Select,
	SelectContent,
	SelectItem,
	SelectTrigger,
	SelectValue,
} from "@/components/shadcn/ui/select";
import { MARK_VALUES, type MarkValue } from "../types";

const UNSELECTED_VALUE = "__unselected__";

type Props = {
	value: MarkValue | null;
	onChange: (value: MarkValue | null) => void;
	ariaLabel?: string;
};

const RaceMarkSelect = ({ value, onChange, ariaLabel }: Props) => {
	return (
		<Select
			value={value ?? UNSELECTED_VALUE}
			onValueChange={(next) => {
				if (next === UNSELECTED_VALUE) {
					onChange(null);
					return;
				}
				onChange(next as MarkValue);
			}}
		>
			<SelectTrigger className="h-8 w-16" aria-label={ariaLabel}>
				<SelectValue />
			</SelectTrigger>
			<SelectContent>
				<SelectItem value={UNSELECTED_VALUE}>―</SelectItem>
				{MARK_VALUES.map((mark) => (
					<SelectItem key={mark} value={mark}>
						{mark}
					</SelectItem>
				))}
			</SelectContent>
		</Select>
	);
};

export default RaceMarkSelect;
