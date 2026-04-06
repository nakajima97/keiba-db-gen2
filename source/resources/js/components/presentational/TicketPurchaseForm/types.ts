import type { TicketTypeId } from "./constants";

export type TicketPurchaseFormProps = {
	// レース情報
	selectedVenue: string;
	selectedRaceDate: string;
	selectedRaceNumber: number;
	// 券種・買い方
	selectedTicketTypeId: TicketTypeId;
	selectedBuyTypeId: string;
	// 軸頭数（三連複nagashiのみ有効）
	selectedAxisCount: 1 | 2;
	// 流し方向（三連単nagashiのみ有効）
	selectedNagashiDirection: 1 | 2 | 3;
	// 馬番選択
	selectedHorses: Record<string, number[]>;
	// 金額
	amount: number;
	// コールバック
	onVenueChange: (venue: string) => void;
	onRaceDateChange: (date: string) => void;
	onRaceNumberChange: (num: number) => void;
	onTicketTypeChange: (id: TicketTypeId) => void;
	onBuyTypeChange: (id: string) => void;
	onAxisCountChange: (count: 1 | 2) => void;
	onNagashiDirectionChange: (pos: 1 | 2 | 3) => void;
	onHorsesChange: (groupKey: string, horses: number[]) => void;
	onAmountChange: (amount: number) => void;
};
