import { useState } from "react";
import { toast } from "sonner";
import type {
	MarkValue,
	RaceMarkValue,
} from "@/features/raceDetail/presentational/RaceDetail/types";
import { upsertMark } from "@/features/raceDetail/requests/raceMarks";

type UseRaceMarksReturn = {
	marks: RaceMarkValue[];
	handleMarkChange: (params: {
		columnId: number;
		raceEntryId: number;
		markValue: MarkValue | null;
	}) => Promise<void>;
	removeByColumnLocally: (columnId: number) => RaceMarkValue[];
	restoreSnapshot: (snapshot: RaceMarkValue[]) => void;
};

/**
 * 印データのローカル state と楽観的 upsert を管理する。
 * markValue が null のときは該当行を削除する。API 失敗時は state を戻して toast 通知する。
 * removeByColumnLocally / restoreSnapshot は印列削除のオーケストレーション用。
 */
export const useRaceMarks = (
	raceUid: string,
	initial: RaceMarkValue[],
): UseRaceMarksReturn => {
	const [marks, setMarks] = useState<RaceMarkValue[]>(initial);

	const handleMarkChange = async (params: {
		columnId: number;
		raceEntryId: number;
		markValue: MarkValue | null;
	}) => {
		const { columnId, raceEntryId, markValue } = params;
		const previous = marks;
		const filtered = marks.filter(
			(m) => !(m.column_id === columnId && m.race_entry_id === raceEntryId),
		);
		const next: RaceMarkValue[] =
			markValue === null
				? filtered
				: [
						...filtered,
						{
							column_id: columnId,
							race_entry_id: raceEntryId,
							mark_value: markValue,
						},
					];
		setMarks(next);
		try {
			await upsertMark(raceUid, columnId, raceEntryId, markValue);
		} catch (_e) {
			setMarks(previous);
			toast.error("印の更新に失敗しました");
		}
	};

	const removeByColumnLocally = (columnId: number): RaceMarkValue[] => {
		const snapshot = marks;
		setMarks((current) => current.filter((m) => m.column_id !== columnId));
		return snapshot;
	};

	const restoreSnapshot = (snapshot: RaceMarkValue[]) => {
		setMarks(snapshot);
	};

	return {
		marks,
		handleMarkChange,
		removeByColumnLocally,
		restoreSnapshot,
	};
};
