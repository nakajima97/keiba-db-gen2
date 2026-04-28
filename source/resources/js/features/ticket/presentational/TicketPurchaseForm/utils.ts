import type { TicketTypeId } from "./constants";

export const getHorseInputConfigKey = (
	ticketTypeId: TicketTypeId,
	buyTypeId: string,
	axisCount: 1 | 2,
	_nagashiDirection: 1 | 2 | 3,
): string => {
	const showNagashiDirectionSelector =
		buyTypeId === "nagashi" && ticketTypeId === "sanrentan";

	if (buyTypeId === "nagashi") {
		return showNagashiDirectionSelector
			? "formation"
			: `nagashi_axis${axisCount}`;
	}
	if (buyTypeId === "formation" && ticketTypeId === "sanrenpuku") {
		return "formation_sanrenpuku";
	}
	return buyTypeId;
};
