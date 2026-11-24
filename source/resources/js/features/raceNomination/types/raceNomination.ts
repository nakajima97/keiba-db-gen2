import type { MarkValue } from "@/features/raceDetail/presentational/RaceDetail/types";

/**
 * 登録馬に対するチェック（check）／消し（excluded）。
 */
export type RaceNominationStatus = "check" | "excluded";

export type RaceNominationNote = {
	id?: number;
	content: string;
	source: "race" | "horse";
};

/**
 * 特別登録・出走登録など、出走確定前の登録馬。
 * status / note は認証ユーザーのもの。
 */
export type RaceNomination = {
	id: number;
	horse_id: number;
	horse_uid: string;
	horse_name: string;
	status: RaceNominationStatus | null;
	note: RaceNominationNote | null;
};

/**
 * 登録馬に対する他人の印（印列は出馬表と共通の race_mark_columns）。
 */
export type RaceNominationColumnMarkValue = {
	column_id: number;
	race_nomination_id: number;
	mark_value: MarkValue;
};

/**
 * 登録馬に対する他人の印の印メモ。
 */
export type RaceNominationColumnMemo = {
	column_id: number;
	race_nomination_id: number;
	content: string;
};

/**
 * 馬名入力欄に表示する競走馬の候補。
 */
export type HorseCandidate = {
	id: number;
	name: string;
	birth_year: number | null;
};
