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
 * 馬名入力欄に表示する競走馬の候補。
 */
export type HorseCandidate = {
	id: number;
	name: string;
	birth_year: number | null;
};
