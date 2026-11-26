import type { ReactNode } from "react";
import type { MarkValue } from "@/features/raceDetail/presentational/RaceDetail/types";
import type {
	RaceNomination,
	RaceNominationColumnMarkValue,
	RaceNominationColumnMemo,
	RaceNominationMarkColumn,
	RaceNominationStatus,
} from "../../types/raceNomination";

export type RaceNominationSectionProps = {
	nominations: ReadonlyArray<RaceNomination>;
	addForm: ReactNode;
	/** 登録馬用の他人の印列（出馬表の印列とは独立） */
	markColumns: ReadonlyArray<RaceNominationMarkColumn>;
	marks: ReadonlyArray<RaceNominationColumnMarkValue>;
	markMemos: ReadonlyArray<RaceNominationColumnMemo>;
	onStatusChange: (
		nominationId: number,
		status: RaceNominationStatus | null,
	) => void;
	onNoteClick: (horseId: number) => void;
	onDeleteClick: (nominationId: number) => void;
	onAddOtherColumn: () => void;
	onChangeColumnLabel: (columnId: number, label: string) => void;
	onRemoveOtherColumn: (columnId: number) => void;
	onMarkChange: (params: {
		columnId: number;
		raceNominationId: number;
		markValue: MarkValue | null;
	}) => void;
	onMarkMemoClick: (params: {
		columnId: number;
		raceNominationId: number;
	}) => void;
};
