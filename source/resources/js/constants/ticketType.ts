/**
 * 馬券種別の name (ticket_types.name と同値)。
 *
 * 並び順は `database/seeders/TicketTypeSeeder.php` の `sort_order` と一致させる。
 * `tanpuku` は本アプリ独自の概念（単勝+複勝の同時購入）であり、JRA 公式券種ではない。
 *
 * @see app/Enums/TicketTypeName.php TicketTypeName と同期させること
 */
export const TICKET_TYPE_NAMES = [
	"tansho",
	"fukusho",
	"wakuren",
	"umaren",
	"umatan",
	"wide",
	"sanrenpuku",
	"sanrentan",
	"tanpuku",
] as const;

export type TicketTypeName = (typeof TICKET_TYPE_NAMES)[number];

export const TICKET_TYPE_LABELS: Record<TicketTypeName, string> = {
	tansho: "単勝",
	fukusho: "複勝",
	wakuren: "枠連",
	umaren: "馬連",
	umatan: "馬単",
	wide: "ワイド",
	sanrenpuku: "三連複",
	sanrentan: "三連単",
	tanpuku: "単複",
};

/** 着順を保持する券種 */
export const ORDERED_TICKET_TYPE_NAMES = ["umatan", "sanrentan"] as const;
