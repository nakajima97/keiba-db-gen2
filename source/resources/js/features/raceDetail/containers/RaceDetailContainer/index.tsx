import { useState } from "react";
import { toast } from "sonner";
import RaceDetail from "@/features/raceDetail/presentational/RaceDetail";
import type {
	MarkValue,
	RaceDetailItem,
	RaceMarkColumn,
	RaceMarkValue,
} from "@/features/raceDetail/presentational/RaceDetail/types";
import {
	createOtherColumn,
	deleteColumn,
	updateColumnLabel,
} from "@/features/raceDetail/requests/raceMarkColumns";
import { upsertMark } from "@/features/raceDetail/requests/raceMarks";
import { useDebouncedCallbackByKey } from "@/hooks/useDebouncedCallback";

const LABEL_DEBOUNCE_MS = 500;

type Props = {
	race: RaceDetailItem;
};

/**
 * レース詳細画面のコンテナ。
 * 印列・印データのローカル state を保持し、ユーザー操作で楽観的に更新→API 呼び出し。
 * API 失敗時は state を元に戻して toast でエラーを通知する。
 * ラベル編集はキー入力中の連打を避けるため列ごとに 500ms デバウンスする。
 */
const RaceDetailContainer = ({ race }: Props) => {
	const [markColumns, setMarkColumns] = useState<RaceMarkColumn[]>(
		race.mark_columns,
	);
	const [marks, setMarks] = useState<RaceMarkValue[]>(race.marks);

	const labelDebouncer = useDebouncedCallbackByKey(
		async (raceUid: string, columnId: number, label: string) => {
			try {
				await updateColumnLabel(raceUid, columnId, label);
			} catch (_e) {
				toast.error("ラベルの更新に失敗しました");
			}
		},
		LABEL_DEBOUNCE_MS,
	);

	const localRace: RaceDetailItem = {
		...race,
		mark_columns: markColumns,
		marks,
	};

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
			const created = await createOtherColumn(race.uid, "");
			setMarkColumns((current) =>
				current.map((c) => (c.id === tempId ? created : c)),
			);
		} catch (_e) {
			setMarkColumns(previous);
			toast.error("印列の追加に失敗しました");
		}
	};

	const handleRemoveOtherColumn = async (columnId: number) => {
		labelDebouncer.cancel(columnId);
		const previousColumns = markColumns;
		const previousMarks = marks;
		setMarkColumns((current) => current.filter((c) => c.id !== columnId));
		setMarks((current) => current.filter((m) => m.column_id !== columnId));
		try {
			await deleteColumn(race.uid, columnId);
		} catch (_e) {
			setMarkColumns(previousColumns);
			setMarks(previousMarks);
			toast.error("印列の削除に失敗しました");
		}
	};

	const handleChangeColumnLabel = (columnId: number, label: string) => {
		setMarkColumns((current) =>
			current.map((c) => (c.id === columnId ? { ...c, label } : c)),
		);
		labelDebouncer.call(columnId, race.uid, columnId, label);
	};

	const handleMarkChange = async (params: {
		columnId: number;
		raceEntryId: number;
		markValue: MarkValue | null;
	}) => {
		const { columnId, raceEntryId, markValue } = params;
		const previous = marks;
		const filtered = marks.filter(
			(m) =>
				!(m.column_id === columnId && m.race_entry_id === raceEntryId),
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
			await upsertMark(race.uid, columnId, raceEntryId, markValue);
		} catch (_e) {
			setMarks(previous);
			toast.error("印の更新に失敗しました");
		}
	};

	return (
		<RaceDetail
			race={localRace}
			onMarkChange={handleMarkChange}
			onAddOtherColumn={handleAddOtherColumn}
			onRemoveOtherColumn={handleRemoveOtherColumn}
			onChangeColumnLabel={handleChangeColumnLabel}
		/>
	);
};

export default RaceDetailContainer;
