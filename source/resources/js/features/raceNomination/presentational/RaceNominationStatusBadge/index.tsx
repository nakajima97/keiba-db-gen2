import { Badge } from "@/components/shadcn/ui/badge";
import { NOMINATION_STATUS_LABELS } from "../../utils/nominationStatus";
import type { RaceNominationStatusBadgeProps } from "./types";

const ARIA_LABELS = {
	check: "登録馬でチェック済み",
	excluded: "登録馬で消し",
} as const;

const RaceNominationStatusBadge = ({
	status,
}: RaceNominationStatusBadgeProps) => {
	return (
		<Badge variant="outline" aria-label={ARIA_LABELS[status]}>
			{NOMINATION_STATUS_LABELS[status]}
		</Badge>
	);
};

export default RaceNominationStatusBadge;

export type { RaceNominationStatusBadgeProps } from "./types";
