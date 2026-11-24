import type { ReactNode } from "react";
import type {
	MarkValue,
	RaceMarkColumn,
} from "@/features/raceDetail/presentational/RaceDetail/types";
import type {
	RaceNomination,
	RaceNominationColumnMarkValue,
	RaceNominationColumnMemo,
	RaceNominationStatus,
} from "../../types/raceNomination";

export type RaceNominationSectionProps = {
	nominations: ReadonlyArray<RaceNomination>;
	addForm: ReactNode;
	/** 出馬表と共通の印列。type=other の列のみ表示する。 */
	markColumns?: ReadonlyArray<RaceMarkColumn>;
	marks?: ReadonlyArray<RaceNominationColumnMarkValue>;
	markMemos?: ReadonlyArray<RaceNominationColumnMemo>;
	onStatusChange: (
		nominationId: number,
		status: RaceNominationStatus | null,
	) => void;
	onNoteClick: (horseId: number) => void;
	onDeleteClick: (nominationId: number) => void;
	onAddOtherColumn?: () => void;
	onChangeColumnLabel?: (columnId: number, label: string) => void;
	onRemoveOtherColumn?: (columnId: number) => void;
	onMarkChange?: (params: {
		columnId: number;
		raceNominationId: number;
		markValue: MarkValue | null;
	}) => void;
	onMarkMemoClick?: (params: {
		columnId: number;
		raceNominationId: number;
	}) => void;
};
