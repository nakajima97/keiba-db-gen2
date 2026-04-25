import type { TicketTypeId } from "../constants";

export type HorseSelectionSectionProps = {
	selectedTicketTypeId: TicketTypeId;
	selectedBuyTypeId: string;
	selectedAxisCount: 1 | 2;
	selectedNagashiDirection: 1 | 2 | 3;
	selectedHorses: Record<string, number[]>;
	onAxisCountChange: (count: 1 | 2) => void;
	onNagashiDirectionChange: (pos: 1 | 2 | 3) => void;
	onHorsesChange: (groupKey: string, horses: number[]) => void;
};
