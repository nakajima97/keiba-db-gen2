import { useMemo, useState } from "react";
import { toast } from "sonner";
import {
	createNomination,
	deleteNomination,
	RaceNominationRequestError,
	upsertNominationMark,
} from "../requests/raceNominations";
import type {
	RaceNomination,
	RaceNominationNote,
	RaceNominationStatus,
} from "../types/raceNomination";
import { buildStatusByHorseId } from "../utils/nominationStatus";

type AddResult = { ok: true } | { ok: false; message: string };

type UseRaceNominationsReturn = {
	nominations: RaceNomination[];
	statusByHorseId: Record<number, RaceNominationStatus>;
	addNomination: (
		input: { horseId: number } | { horseName: string },
	) => Promise<AddResult>;
	removeNomination: (nominationId: number) => Promise<boolean>;
	changeStatus: (
		nominationId: number,
		status: RaceNominationStatus | null,
	) => Promise<void>;
	applyNote: (horseId: number, note: RaceNominationNote) => void;
};

/**
 * 登録馬のローカル state と追加・削除・チェック／消し変更を管理する。
 * チェック／消しは楽観的更新し、API 失敗時は元に戻して toast 通知する。
 */
export const useRaceNominations = (
	raceUid: string,
	initial: RaceNomination[],
): UseRaceNominationsReturn => {
	const [nominations, setNominations] = useState<RaceNomination[]>(initial);

	const statusByHorseId = useMemo(
		() => buildStatusByHorseId(nominations),
		[nominations],
	);

	const addNomination = async (
		input: { horseId: number } | { horseName: string },
	): Promise<AddResult> => {
		try {
			const created = await createNomination(raceUid, input);
			setNominations((current) => [...current, created]);
			return { ok: true };
		} catch (e) {
			const message =
				e instanceof RaceNominationRequestError
					? e.message
					: "登録馬の追加に失敗しました";
			return { ok: false, message };
		}
	};

	const removeNomination = async (nominationId: number): Promise<boolean> => {
		try {
			await deleteNomination(raceUid, nominationId);
			setNominations((current) =>
				current.filter((nomination) => nomination.id !== nominationId),
			);
			return true;
		} catch (_e) {
			toast.error("登録馬の削除に失敗しました");
			return false;
		}
	};

	const changeStatus = async (
		nominationId: number,
		status: RaceNominationStatus | null,
	) => {
		const previous = nominations;
		setNominations((current) =>
			current.map((nomination) =>
				nomination.id === nominationId ? { ...nomination, status } : nomination,
			),
		);
		try {
			await upsertNominationMark(raceUid, nominationId, status);
		} catch (_e) {
			setNominations(previous);
			toast.error("チェック／消しの更新に失敗しました");
		}
	};

	const applyNote = (horseId: number, note: RaceNominationNote) => {
		setNominations((current) =>
			current.map((nomination) =>
				nomination.horse_id === horseId ? { ...nomination, note } : nomination,
			),
		);
	};

	return {
		nominations,
		statusByHorseId,
		addNomination,
		removeNomination,
		changeStatus,
		applyNote,
	};
};
