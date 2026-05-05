import { useState } from "react";
import type { RaceMarkMemo } from "@/features/raceDetail/presentational/RaceDetail/types";

type UseRaceMarkMemosReturn = {
	markMemos: RaceMarkMemo[];
	handleMemoSaved: (params: {
		columnId: number;
		raceEntryId: number;
		content: string;
	}) => void;
	handleMemoDeleted: (params: {
		columnId: number;
		raceEntryId: number;
	}) => void;
	removeByColumnLocally: (columnId: number) => RaceMarkMemo[];
	restoreSnapshot: (snapshot: RaceMarkMemo[]) => void;
};

/**
 * 印メモのローカル state を管理する。
 * API 呼び出しは RaceMarkMemoModalContainer 側が担うため、本フックは保存/削除完了の通知を受けて state を同期するだけ。
 * removeByColumnLocally / restoreSnapshot は印列削除のオーケストレーション用。
 */
export const useRaceMarkMemos = (
	initial: RaceMarkMemo[],
): UseRaceMarkMemosReturn => {
	const [markMemos, setMarkMemos] = useState<RaceMarkMemo[]>(initial);

	const handleMemoSaved = (params: {
		columnId: number;
		raceEntryId: number;
		content: string;
	}) => {
		setMarkMemos((current) => {
			const filtered = current.filter(
				(m) =>
					!(
						m.column_id === params.columnId &&
						m.race_entry_id === params.raceEntryId
					),
			);
			return [
				...filtered,
				{
					column_id: params.columnId,
					race_entry_id: params.raceEntryId,
					content: params.content,
				},
			];
		});
	};

	const handleMemoDeleted = (params: {
		columnId: number;
		raceEntryId: number;
	}) => {
		setMarkMemos((current) =>
			current.filter(
				(m) =>
					!(
						m.column_id === params.columnId &&
						m.race_entry_id === params.raceEntryId
					),
			),
		);
	};

	const removeByColumnLocally = (columnId: number): RaceMarkMemo[] => {
		const snapshot = markMemos;
		setMarkMemos((current) => current.filter((m) => m.column_id !== columnId));
		return snapshot;
	};

	const restoreSnapshot = (snapshot: RaceMarkMemo[]) => {
		setMarkMemos(snapshot);
	};

	return {
		markMemos,
		handleMemoSaved,
		handleMemoDeleted,
		removeByColumnLocally,
		restoreSnapshot,
	};
};
