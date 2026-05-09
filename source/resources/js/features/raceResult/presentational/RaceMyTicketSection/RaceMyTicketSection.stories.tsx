import type { Meta, StoryObj } from "@storybook/react-vite";
import RaceMyTicketSection from ".";
import type { RaceMyTicketSectionProps } from ".";

const meta: Meta<typeof RaceMyTicketSection> = {
	title: "features/raceResult/presentational/RaceMyTicketSection",
	component: RaceMyTicketSection,
};

export default meta;
type Story = StoryObj<typeof RaceMyTicketSection>;

const sampleTickets: RaceMyTicketSectionProps["tickets"] = [
	{
		id: 1,
		ticket_type_label: "単勝",
		buy_type_name: "single",
		buy_type_label: "通常",
		selections: { horses: [3] },
		purchase_amount: 100,
		payout_amount: null,
	},
	{
		id: 2,
		ticket_type_label: "馬連",
		buy_type_name: "nagashi",
		buy_type_label: "流し",
		selections: { axis: [1], others: [2, 4, 6] },
		purchase_amount: 300,
		payout_amount: null,
	},
	{
		id: 3,
		ticket_type_label: "三連複",
		buy_type_name: "box",
		buy_type_label: "ボックス",
		selections: { horses: [1, 3, 5] },
		purchase_amount: 300,
		payout_amount: null,
	},
	{
		id: 4,
		ticket_type_label: "三連単",
		buy_type_name: "formation",
		buy_type_label: "フォーメーション",
		selections: {
			columns: [
				[1, 2],
				[1, 3],
				[4, 5],
			],
		},
		purchase_amount: 3600,
		payout_amount: null,
	},
];

export const Default: Story = {
	name: "複数馬券（single/nagashi/box/formationパターン）",
	args: {
		tickets: sampleTickets,
	},
};

export const WithHit: Story = {
	name: "的中馬券あり",
	args: {
		tickets: [
			{ ...sampleTickets[0], payout_amount: 610 },
			{ ...sampleTickets[1], payout_amount: 1350 },
			{ ...sampleTickets[2], payout_amount: 0 },
			{ ...sampleTickets[3], payout_amount: null },
		],
	},
};

export const WithMiss: Story = {
	name: "ハズレ馬券のみ",
	args: {
		tickets: [
			{ ...sampleTickets[0], payout_amount: 0 },
			{ ...sampleTickets[1], payout_amount: 0 },
			{ ...sampleTickets[2], payout_amount: 0 },
			{ ...sampleTickets[3], payout_amount: 0 },
		],
	},
};
