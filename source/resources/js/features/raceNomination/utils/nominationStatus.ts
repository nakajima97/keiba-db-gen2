import type {
	RaceNomination,
	RaceNominationStatus,
} from "../types/raceNomination";

/**
 * 登録馬リストから horse_id → チェック／消し の対応表を作る。
 * 出走確定後の出馬表で、同じ馬に付けたチェック／消しを引き継いで表示するために使う。
 * status が未設定（null）の登録馬は含めない。
 */
export const buildStatusByHorseId = (
	nominations: ReadonlyArray<RaceNomination>,
): Record<number, RaceNominationStatus> => {
	const result: Record<number, RaceNominationStatus> = {};
	for (const nomination of nominations) {
		if (nomination.status !== null) {
			result[nomination.horse_id] = nomination.status;
		}
	}
	return result;
};

/**
 * チェック／消しの表示ラベル。
 */
export const NOMINATION_STATUS_LABELS: Record<RaceNominationStatus, string> = {
	check: "✓",
	excluded: "消",
};
