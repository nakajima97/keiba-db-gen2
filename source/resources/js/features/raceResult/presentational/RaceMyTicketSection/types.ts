export type Selections =
	| { horses: number[] }
	| { axis: number[]; others?: number[] }
	| { columns: number[][] };

export type RaceMyTicket = {
	id: number;
	ticket_type_label: string;
	buy_type_name: "single" | "nagashi" | "box" | "formation";
	buy_type_label: string;
	selections: Selections;
	purchase_amount: number | null;
	payout_amount: number | null;
};

export type RaceMyTicketSectionProps = {
	tickets: RaceMyTicket[];
};
