import { useState } from "react";
import type { RaceNominationColumnMemo } from "../types/raceNomination";

type UseRaceNominationColumnMemosReturn = {
	markMemos: RaceNominationColumnMemo[];
	handleMemoSaved: (params: {
		columnId: number;
		raceNominationId: number;
		content: string;
	}) => void;
	handleMemoDeleted: (params: {
		columnId: number;
		raceNominationId: number;
	}) => void;
	removeByColumnLocally: (columnId: number) => RaceNominationColumnMemo[];
	restoreSnapshot: (snapshot: RaceNominationColumnMemo[]) => void;
};

const isSameCell = (
	memo: RaceNominationColumnMemo,
	columnId: number,
	raceNominationId: number,
): boolean =>
	memo.column_id === columnId && memo.race_nomination_id === raceNominationId;

/**
 * 登録馬の印メモのローカル state を管理する。
 * API 呼び出しは RaceMarkMemoModalContainer 側が担うため、本フックは保存/削除完了の通知を受けて state を同期するだけ。
 * removeByColumnLocally / restoreSnapshot は印列削除のオーケストレーション用。
 */
export const useRaceNominationColumnMemos = (
	initial: RaceNominationColumnMemo[],
): UseRaceNominationColumnMemosReturn => {
	const [markMemos, setMarkMemos] =
		useState<RaceNominationColumnMemo[]>(initial);

	const handleMemoSaved = (params: {
		columnId: number;
		raceNominationId: number;
		content: string;
	}) => {
		setMarkMemos((current) => [
			...current.filter(
				(memo) => !isSameCell(memo, params.columnId, params.raceNominationId),
			),
			{
				column_id: params.columnId,
				race_nomination_id: params.raceNominationId,
				content: params.content,
			},
		]);
	};

	const handleMemoDeleted = (params: {
		columnId: number;
		raceNominationId: number;
	}) => {
		setMarkMemos((current) =>
			current.filter(
				(memo) => !isSameCell(memo, params.columnId, params.raceNominationId),
			),
		);
	};

	const removeByColumnLocally = (
		columnId: number,
	): RaceNominationColumnMemo[] => {
		const snapshot = markMemos;
		setMarkMemos((current) =>
			current.filter((memo) => memo.column_id !== columnId),
		);
		return snapshot;
	};

	const restoreSnapshot = (snapshot: RaceNominationColumnMemo[]) => {
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
