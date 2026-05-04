import { TICKET_TYPE_LABELS, TICKET_TYPE_NAMES } from "@/constants/ticketType";

export const VENUES = [
	"東京",
	"中山",
	"阪神",
	"京都",
	"新潟",
	"福島",
	"小倉",
	"函館",
	"札幌",
	"中京",
] as const;

/** UI 表示対象の券種 (tanpuku は本アプリ独自概念のため UI からは選択させない) */
export type TicketTypeId = Exclude<
	(typeof TICKET_TYPE_NAMES)[number],
	"tanpuku"
>;

export const TICKET_TYPES: ReadonlyArray<{ id: TicketTypeId; label: string }> =
	TICKET_TYPE_NAMES.filter((id): id is TicketTypeId => id !== "tanpuku").map(
		(id) => ({
			id,
			label: TICKET_TYPE_LABELS[id],
		}),
	);

export const BUY_TYPE_MAP: Record<
	TicketTypeId,
	{ id: string; label: string }[]
> = {
	tansho: [{ id: "single", label: "通常" }],
	fukusho: [{ id: "single", label: "通常" }],
	wakuren: [
		{ id: "nagashi", label: "流し" },
		{ id: "box", label: "ボックス" },
	],
	umaren: [
		{ id: "nagashi", label: "流し" },
		{ id: "box", label: "ボックス" },
	],
	umatan: [
		{ id: "nagashi", label: "流し" },
		{ id: "box", label: "ボックス" },
		{ id: "formation", label: "フォーメーション" },
	],
	wide: [
		{ id: "nagashi", label: "流し" },
		{ id: "box", label: "ボックス" },
	],
	sanrenpuku: [
		{ id: "nagashi", label: "流し" },
		{ id: "box", label: "ボックス" },
		{ id: "formation", label: "フォーメーション" },
	],
	sanrentan: [
		{ id: "nagashi", label: "流し" },
		{ id: "box", label: "ボックス" },
		{ id: "formation", label: "フォーメーション" },
	],
};

// 券種ごとのグリッドサイズ（枠連は枠番1〜8、それ以外は馬番1〜18）
export const GRID_SIZE: Partial<Record<TicketTypeId, number>> = {
	wakuren: 8,
};

// 買い方 × 軸頭数ごとのグループ構成
export const HORSE_INPUT_CONFIG: Record<
	string,
	{ key: string; label: string }[]
> = {
	single: [{ key: "horses", label: "馬番" }],
	box: [{ key: "horses", label: "馬番" }],
	nagashi_axis1: [
		{ key: "axis", label: "軸" },
		{ key: "others", label: "相手" },
	],
	nagashi_axis2: [
		{ key: "axis1", label: "軸1" },
		{ key: "axis2", label: "軸2" },
		{ key: "others", label: "相手" },
	],
	// 三連単流し・三連単フォーメーション（パターン④）
	formation: [
		{ key: "col1", label: "1着" },
		{ key: "col2", label: "2着" },
		{ key: "col3", label: "3着" },
	],
	// 三連複フォーメーション（着順不問のため「列」ラベル）
	formation_sanrenpuku: [
		{ key: "col1", label: "1列目" },
		{ key: "col2", label: "2列目" },
		{ key: "col3", label: "3列目" },
	],
};
