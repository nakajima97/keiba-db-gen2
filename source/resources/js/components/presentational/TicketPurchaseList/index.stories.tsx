import type { Meta, StoryObj } from "@storybook/react-vite";
import TicketPurchaseList from ".";
import type { TicketPurchaseListProps } from ".";

const meta: Meta<typeof TicketPurchaseList> = {
	title: "TicketPurchaseList",
	component: TicketPurchaseList,
};

export default meta;
type Story = StoryObj<typeof TicketPurchaseList>;

const baseArgs: Pick<
	TicketPurchaseListProps,
	"hasMore" | "isLoading" | "onLoadMore"
> = {
	hasMore: false,
	isLoading: false,
	onLoadMore: () => {},
};

const samplePurchases: TicketPurchaseListProps["purchases"] = [
	{
		id: 1,
		race_uid: "abc123",
		has_race_result: true,
		race_date: "2026-04-05",
		venue_name: "東京",
		race_number: 1,
		ticket_type_label: "馬連",
		buy_type_name: "nagashi",
		selections: { axis: [1], others: [2, 4, 6] },
		amount: 100,
		payout_amount: null,
	},
	{
		id: 2,
		race_uid: "abc123",
		has_race_result: true,
		race_date: "2026-04-05",
		venue_name: "東京",
		race_number: 1,
		ticket_type_label: "三連複",
		buy_type_name: "box",
		selections: { horses: [1, 3, 5] },
		amount: 300,
		payout_amount: null,
	},
	{
		id: 3,
		race_uid: "def456",
		has_race_result: false,
		race_date: "2026-04-05",
		venue_name: "中山",
		race_number: 11,
		ticket_type_label: "三連単",
		buy_type_name: "formation",
		selections: {
			columns: [
				[1, 2],
				[3, 4],
				[5, 6, 7],
			],
		},
		amount: 600,
		payout_amount: null,
	},
	{
		id: 4,
		race_uid: "ghi789",
		has_race_result: false,
		race_date: "2026-04-04",
		venue_name: "阪神",
		race_number: 8,
		ticket_type_label: "単勝",
		buy_type_name: "single",
		selections: { horses: [5] },
		amount: null,
		payout_amount: null,
	},
	{
		id: 5,
		race_uid: null,
		has_race_result: false,
		race_date: null,
		venue_name: null,
		race_number: null,
		ticket_type_label: "複勝",
		buy_type_name: "single",
		selections: { horses: [3] },
		amount: 200,
		payout_amount: null,
	},
];

const samplePurchasesWithPayout: TicketPurchaseListProps["purchases"] = [
	{ ...samplePurchases[0], payout_amount: 5000 },
	{ ...samplePurchases[1], payout_amount: null },
	{ ...samplePurchases[2], payout_amount: null },
	{ ...samplePurchases[3], payout_amount: null },
	{ ...samplePurchases[4], payout_amount: null },
];

export const Empty: Story = {
	name: "空の状態",
	args: {
		...baseArgs,
		purchases: [],
	},
};

export const WithData: Story = {
	name: "データあり",
	args: {
		...baseArgs,
		purchases: samplePurchases,
	},
};

export const WithDataHasMore: Story = {
	name: "データあり（追加読み込み可能）",
	args: {
		...baseArgs,
		purchases: samplePurchases,
		hasMore: true,
	},
};

export const LoadingMore: Story = {
	name: "追加読み込み中",
	args: {
		...baseArgs,
		purchases: samplePurchases,
		hasMore: true,
		isLoading: true,
	},
};

export const WithPayoutAmount: Story = {
	name: "払い戻し金額あり（当たり）",
	args: {
		...baseArgs,
		purchases: samplePurchasesWithPayout,
	},
};

export const WithNoPayoutAmount: Story = {
	name: "払い戻し金額なし（外れ）",
	args: {
		...baseArgs,
		purchases: samplePurchases,
	},
};
