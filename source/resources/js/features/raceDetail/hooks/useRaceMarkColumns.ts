import { useState } from "react";
import { toast } from "sonner";
import type { RaceMarkColumn } from "@/features/raceDetail/presentational/RaceDetail/types";
import {
	createOtherColumn,
	updateColumnLabel,
} from "@/features/raceDetail/requests/raceMarkColumns";
import { useDebouncedCallbackByKey } from "@/hooks/useDebouncedCallback";

const LABEL_DEBOUNCE_MS = 500;

type UseRaceMarkColumnsReturn = {
	markColumns: RaceMarkColumn[];
	handleAddOtherColumn: () => Promise<void>;
	handleChangeColumnLabel: (columnId: number, label: string) => void;
	removeColumnLocally: (columnId: number) => RaceMarkColumn[];
	restoreColumns: (snapshot: RaceMarkColumn[]) => void;
	cancelLabelDebounce: (columnId: number) => void;
};

/**
 * 印列のローカル state と楽観的更新を管理する。
 * 列追加（API 失敗時はロールバック）、ラベル編集（500ms デバウンス API）、ラベルデバウンスのキャンセルを提供する。
 * 列削除そのものは marks/markMemos との横断ロールバックが必要なので、本フックは removeColumnLocally / restoreColumns を提供しコンテナでオーケストレーションする。
 */
export const useRaceMarkColumns = (
	raceUid: string,
	initial: RaceMarkColumn[],
): UseRaceMarkColumnsReturn => {
	const [markColumns, setMarkColumns] = useState<RaceMarkColumn[]>(initial);

	const labelDebouncer = useDebouncedCallbackByKey(
		async (uid: string, columnId: number, label: string) => {
			try {
				await updateColumnLabel(uid, columnId, label);
			} catch (_e) {
				toast.error("ラベルの更新に失敗しました");
			}
		},
		LABEL_DEBOUNCE_MS,
	);

	const handleAddOtherColumn = async () => {
		const tempId = -Date.now();
		const maxOrder = markColumns.reduce(
			(acc, c) => (c.display_order > acc ? c.display_order : acc),
			0,
		);
		const optimistic: RaceMarkColumn = {
			id: tempId,
			type: "other",
			label: "",
			display_order: maxOrder + 1,
		};
		const previous = markColumns;
		setMarkColumns([...previous, optimistic]);
		try {
			const created = await createOtherColumn(raceUid, "");
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

	const removeColumnLocally = (columnId: number): RaceMarkColumn[] => {
		const snapshot = markColumns;
		setMarkColumns((current) => current.filter((c) => c.id !== columnId));
		return snapshot;
	};

	const restoreColumns = (snapshot: RaceMarkColumn[]) => {
		setMarkColumns(snapshot);
	};

	const cancelLabelDebounce = (columnId: number) => {
		labelDebouncer.cancel(columnId);
	};

	return {
		markColumns,
		handleAddOtherColumn,
		handleChangeColumnLabel,
		removeColumnLocally,
		restoreColumns,
		cancelLabelDebounce,
	};
};
