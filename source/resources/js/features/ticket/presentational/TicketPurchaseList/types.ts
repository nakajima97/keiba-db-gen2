export type Selections =
	| { horses: number[] }
	| { axis: number[]; others?: number[] }
	| { axis1: number[]; axis2?: number[]; others?: number[] }
	| { col1: number[]; col2?: number[]; col3?: number[] }
	| { columns: number[][] };

export type TicketPurchaseListItem = {
	id: number;
	race_uid: string | null;
	has_race_result: boolean;
	race_date: string | null;
	venue_name: string | null;
	race_number: number | null;
	ticket_type_label: string;
	buy_type_name: "single" | "nagashi" | "box" | "formation";
	selections: Selections;
	num_combinations: number;
	purchase_amount: number | null;
	payout_amount: number | null;
};

export type TicketPurchaseListProps = {
	purchases: TicketPurchaseListItem[];
	hasMore: boolean;
	isLoading: boolean;
	onLoadMore: () => void;
};
