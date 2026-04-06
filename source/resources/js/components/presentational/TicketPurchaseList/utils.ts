import type { Selections } from "./types";

/**
 * 馬番選択情報を一覧表示用の文字列に変換する。
 * - horses形式（single/box）: "1-3-5"
 * - axis/others形式（nagashi）: "1 - 2, 4, 6"
 * - columns形式（formation）: "1,2 - 3,4 - 5,6,7"
 * @param selections - DBから取得した馬番選択情報（JSON）
 */
export function formatSelections(selections: Selections): string {
	if ("horses" in selections) {
		return selections.horses.join("-");
	}
	if ("axis" in selections) {
		return `${selections.axis.join(", ")} - ${selections.others.join(", ")}`;
	}
	return selections.columns.map((col) => col.join(",")).join(" - ");
}
