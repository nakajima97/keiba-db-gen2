import {
	ToggleGroup,
	ToggleGroupItem,
} from "@/components/shadcn/ui/toggle-group";
import type { RaceNominationStatus } from "../../types/raceNomination";
import { NOMINATION_STATUS_LABELS } from "../../utils/nominationStatus";
import type { RaceNominationStatusToggleProps } from "./types";

const isNominationStatus = (value: string): value is RaceNominationStatus =>
	value === "check" || value === "excluded";

const RaceNominationStatusToggle = ({
	value,
	horseName,
	onChange,
}: RaceNominationStatusToggleProps) => {
	return (
		<ToggleGroup
			type="single"
			variant="outline"
			size="sm"
			value={value ?? ""}
			onValueChange={(next) => onChange(isNominationStatus(next) ? next : null)}
			aria-label={`${horseName}のチェック／消し`}
		>
			<ToggleGroupItem value="check" aria-label={`${horseName}をチェック`}>
				{NOMINATION_STATUS_LABELS.check}
			</ToggleGroupItem>
			<ToggleGroupItem value="excluded" aria-label={`${horseName}を消し`}>
				{NOMINATION_STATUS_LABELS.excluded}
			</ToggleGroupItem>
		</ToggleGroup>
	);
};

export default RaceNominationStatusToggle;

export type { RaceNominationStatusToggleProps } from "./types";
