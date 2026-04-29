import type { Selections } from "./types";

/**
 * 馬番選択情報を一覧表示用の文字列に変換する。
 * - horses形式（single/box）: "1-3-5"
 * - axis/others形式（nagashi1軸）: "1 - 2, 4, 6"
 * - axis1/axis2/others形式（nagashi2軸）: "1, 2 - 3, 4, 6"
 * - col1/col2/col3形式（formation）: "1,2 - 3,4 - 5,6,7"
 * - columns形式: "1,2 - 3,4 - 5,6,7"
 * @param selections - DBから取得した馬番選択情報（JSON）
 */
export const formatSelections = (selections: Selections): string => {
	if ("horses" in selections) {
		return selections.horses.join("-");
	}
	if ("axis1" in selections) {
		const axis2Part = selections.axis2?.join(", ");
		const axisPart = axis2Part
			? `${selections.axis1.join(", ")} / ${axis2Part}`
			: selections.axis1.join(", ");
		const othersPart = selections.others?.join(", ");
		return othersPart ? `${axisPart} - ${othersPart}` : axisPart;
	}
	if ("axis" in selections) {
		const othersPart = selections.others?.join(", ");
		return othersPart
			? `${selections.axis.join(", ")} - ${othersPart}`
			: selections.axis.join(", ");
	}
	if ("col1" in selections) {
		return [selections.col1, selections.col2, selections.col3]
			.filter((col): col is number[] => col !== undefined && col.length > 0)
			.map((col) => col.join(","))
			.join(" - ");
	}
	return selections.columns.map((col) => col.join(",")).join(" - ");
};
