const ARROW_TICKET_TYPES = ["umatan", "sanrentan"];

/**
 * 馬番リストを指定した券種の表示形式で連結した文字列を返す。
 * sort_order 昇順に並び替えたうえで連結する。
 * 馬単・三連単は "→"、それ以外は "-" を区切り文字として使用する。
 */
export function formatHorseNumbers(
	horses: { horse_number: number; sort_order: number }[],
	ticketTypeName: string,
): string {
	const sorted = [...horses].sort((a, b) => a.sort_order - b.sort_order);
	const numbers = sorted.map((h) => h.horse_number);
	const separator = ARROW_TICKET_TYPES.includes(ticketTypeName) ? "→" : "-";
	return numbers.join(separator);
}
