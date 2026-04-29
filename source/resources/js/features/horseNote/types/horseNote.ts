/**
 * バックエンド API が返す紐づきレース情報。
 * メモがレースに紐づいていない場合は null。
 */
export type HorseNoteRaceInfo = {
	uid: string;
	race_date: string;
	venue_name: string;
	race_number: number;
	race_name: string | null;
};

/**
 * バックエンド API が返す競走馬メモのリソース。
 */
export type HorseNote = {
	id: number;
	horse_id: number;
	race_id: number | null;
	race: HorseNoteRaceInfo | null;
	content: string;
	created_at: string;
	updated_at: string;
};
