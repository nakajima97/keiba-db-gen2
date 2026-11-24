import { useState } from "react";
import { toast } from "sonner";
import type { MarkValue } from "@/features/raceDetail/presentational/RaceDetail/types";
import { upsertNominationColumnMark } from "../requests/raceNominationColumnMarks";
import type { RaceNominationColumnMarkValue } from "../types/raceNomination";

type UseRaceNominationColumnMarksReturn = {
	marks: RaceNominationColumnMarkValue[];
	handleMarkChange: (params: {
		columnId: number;
		raceNominationId: number;
		markValue: MarkValue | null;
	}) => Promise<void>;
	removeByColumnLocally: (columnId: number) => RaceNominationColumnMarkValue[];
	restoreSnapshot: (snapshot: RaceNominationColumnMarkValue[]) => void;
};

const isSameCell = (
	mark: RaceNominationColumnMarkValue,
	columnId: number,
	raceNominationId: number,
): boolean =>
	mark.column_id === columnId && mark.race_nomination_id === raceNominationId;

/**
 * 登録馬の他人の印のローカル state と楽観的 upsert を管理する。
 * markValue が null のときは該当行を削除する。API 失敗時はそのセルを元の値に戻して toast 通知する。
 * removeByColumnLocally / restoreSnapshot は印列削除のオーケストレーション用。
 */
export const useRaceNominationColumnMarks = (
	raceUid: string,
	initial: RaceNominationColumnMarkValue[],
): UseRaceNominationColumnMarksReturn => {
	const [marks, setMarks] = useState<RaceNominationColumnMarkValue[]>(initial);

	const handleMarkChange = async (params: {
		columnId: number;
		raceNominationId: number;
		markValue: MarkValue | null;
	}) => {
		const { columnId, raceNominationId, markValue } = params;
		const previousMark = marks.find((mark) =>
			isSameCell(mark, columnId, raceNominationId),
		);
		const replaceCell = (
			current: RaceNominationColumnMarkValue[],
			nextMark: RaceNominationColumnMarkValue | undefined,
		): RaceNominationColumnMarkValue[] => {
			const filtered = current.filter(
				(mark) => !isSameCell(mark, columnId, raceNominationId),
			);
			return nextMark === undefined ? filtered : [...filtered, nextMark];
		};

		setMarks((current) =>
			replaceCell(
				current,
				markValue === null
					? undefined
					: {
							column_id: columnId,
							race_nomination_id: raceNominationId,
							mark_value: markValue,
						},
			),
		);
		try {
			await upsertNominationColumnMark(
				raceUid,
				columnId,
				raceNominationId,
				markValue,
			);
		} catch (_e) {
			setMarks((current) => replaceCell(current, previousMark));
			toast.error("印の更新に失敗しました");
		}
	};

	const removeByColumnLocally = (
		columnId: number,
	): RaceNominationColumnMarkValue[] => {
		const snapshot = marks;
		setMarks((current) => current.filter((mark) => mark.column_id !== columnId));
		return snapshot;
	};

	const restoreSnapshot = (snapshot: RaceNominationColumnMarkValue[]) => {
		setMarks(snapshot);
	};

	return {
		marks,
		handleMarkChange,
		removeByColumnLocally,
		restoreSnapshot,
	};
};
