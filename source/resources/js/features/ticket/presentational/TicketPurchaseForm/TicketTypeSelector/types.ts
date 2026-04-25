import type { TicketTypeId } from "../constants";

export type TicketTypeSelectorProps = {
	selectedTicketTypeId: TicketTypeId;
	selectedBuyTypeId: string;
	onTicketTypeChange: (id: TicketTypeId) => void;
	onBuyTypeChange: (id: string) => void;
};
