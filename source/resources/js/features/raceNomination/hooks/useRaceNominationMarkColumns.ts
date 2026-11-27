import { useState } from "react";
import { toast } from "sonner";
import { useDebouncedCallbackByKey } from "@/hooks/useDebouncedCallback";
import {
	createNominationMarkColumn,
	updateNominationMarkColumnLabel,
} from "../requests/raceNominationMarkColumns";
import type { RaceNominationMarkColumn } from "../types/raceNomination";

const LABEL_DEBOUNCE_MS = 500;

type UseRaceNominationMarkColumnsReturn = {
	markColumns: RaceNominationMarkColumn[];
	handleAddColumn: () => Promise<void>;
	handleChangeColumnLabel: (columnId: number, label: string) => void;
	removeColumnLocally: (columnId: number) => RaceNominationMarkColumn[];
	restoreColumns: (snapshot: RaceNominationMarkColumn[]) => void;
	cancelLabelDebounce: (columnId: number) => void;
};

/**
 * 登録馬用の他人の印列のローカル state と楽観的更新を管理する。出馬表の印列（useRaceMarkColumns）とは独立している。
 * 列追加（API 失敗時はロールバック）、ラベル編集（500ms デバウンス API）、ラベルデバウンスのキャンセルを提供する。
 * 列削除は印・印メモとの横断ロールバックが必要なので、removeColumnLocally / restoreColumns を提供しコンテナでオーケストレーションする。
 */
export const useRaceNominationMarkColumns = (
	raceUid: string,
	initial: RaceNominationMarkColumn[],
): UseRaceNominationMarkColumnsReturn => {
	const [markColumns, setMarkColumns] =
		useState<RaceNominationMarkColumn[]>(initial);

	const labelDebouncer = useDebouncedCallbackByKey(
		async (uid: string, columnId: number, label: string) => {
			try {
				await updateNominationMarkColumnLabel(uid, columnId, label);
			} catch (_e) {
				toast.error("ラベルの更新に失敗しました");
			}
		},
		LABEL_DEBOUNCE_MS,
	);

	const handleAddColumn = async () => {
		const tempId = -Date.now();
		const maxOrder = markColumns.reduce(
			(acc, c) => (c.display_order > acc ? c.display_order : acc),
			0,
		);
		const optimistic: RaceNominationMarkColumn = {
			id: tempId,
			label: "",
			display_order: maxOrder + 1,
		};
		const previous = markColumns;
		setMarkColumns([...previous, optimistic]);
		try {
			const created = await createNominationMarkColumn(raceUid, "");
			setMarkColumns((current) =>
				current.map((c) => (c.id === tempId ? created : c)),
			);
		} catch (_e) {
			setMarkColumns(previous);
			toast.error("印列の追加に失敗しました");
		}
	};

	const handleChangeColumnLabel = (columnId: number, label: string) => {
		setMarkColumns((current) =>
			current.map((c) => (c.id === columnId ? { ...c, label } : c)),
		);
		labelDebouncer.call(columnId, raceUid, columnId, label);
	};

	const removeColumnLocally = (
		columnId: number,
	): RaceNominationMarkColumn[] => {
		const snapshot = markColumns;
		setMarkColumns((current) => current.filter((c) => c.id !== columnId));
		return snapshot;
	};

	const restoreColumns = (snapshot: RaceNominationMarkColumn[]) => {
		setMarkColumns(snapshot);
	};

	const cancelLabelDebounce = (columnId: number) => {
		labelDebouncer.cancel(columnId);
	};

	return {
		markColumns,
		handleAddColumn,
		handleChangeColumnLabel,
		removeColumnLocally,
		restoreColumns,
		cancelLabelDebounce,
	};
};
