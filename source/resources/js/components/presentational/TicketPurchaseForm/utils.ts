import type { TicketTypeId } from "./constants";

export function getHorseInputConfigKey(
	ticketTypeId: TicketTypeId,
	buyTypeId: string,
	axisCount: 1 | 2,
	_nagashiDirection: 1 | 2 | 3,
): string {
	const showNagashiDirectionSelector =
		buyTypeId === "nagashi" && ticketTypeId === "sanrentan";

	if (buyTypeId === "nagashi") {
		return showNagashiDirectionSelector
			? "formation"
			: `nagashi_axis${axisCount}`;
	}
	return buyTypeId;
}
