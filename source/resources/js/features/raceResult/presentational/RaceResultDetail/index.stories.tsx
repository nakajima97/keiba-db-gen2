import type { Meta, StoryObj } from "@storybook/react-vite";
import RaceResultDetail from ".";
import type { RaceResultDetailProps } from ".";

const meta: Meta<typeof RaceResultDetail> = {
	title: "features/raceResult/presentational/RaceResultDetail",
	component: RaceResultDetail,
};

export default meta;
type Story = StoryObj<typeof RaceResultDetail>;

const baseRace: Pick<
	RaceResultDetailProps["race"],
	"uid" | "venue_name" | "race_date" | "race_number"
> = {
	uid: "abc001",
	venue_name: "東京",
	race_date: "2026-04-19",
	race_number: 11,
};

const allPayouts: RaceResultDetailProps["race"]["payouts"] = [
	{
		ticket_type_label: "単勝",
		ticket_type_name: "tansho",
		payout_amount: 610,
		popularity: 2,
		horses: [{ horse_number: 3, sort_order: 1 }],
	},
	{
		ticket_type_label: "複勝",
		ticket_type_name: "fukusho",
		payout_amount: 180,
		popularity: 1,
		horses: [{ horse_number: 3, sort_order: 1 }],
	},
	{
		ticket_type_label: "複勝",
		ticket_type_name: "fukusho",
		payout_amount: 340,
		popularity: 3,
		horses: [{ horse_number: 5, sort_order: 1 }],
	},
	{
		ticket_type_label: "複勝",
		ticket_type_name: "fukusho",
		payout_amount: 450,
		popularity: 4,
		horses: [{ horse_number: 8, sort_order: 1 }],
	},
	{
		ticket_type_label: "枠連",
		ticket_type_name: "wakuren",
		payout_amount: 820,
		popularity: 3,
		horses: [
			{ horse_number: 3, sort_order: 1 },
			{ horse_number: 5, sort_order: 2 },
		],
	},
	{
		ticket_type_label: "馬連",
		ticket_type_name: "umaren",
		payout_amount: 1350,
		popularity: 2,
		horses: [
			{ horse_number: 3, sort_order: 1 },
			{ horse_number: 5, sort_order: 2 },
		],
	},
	{
		ticket_type_label: "馬単",
		ticket_type_name: "umatan",
		payout_amount: 2410,
		popularity: 3,
		horses: [
			{ horse_number: 3, sort_order: 1 },
			{ horse_number: 5, sort_order: 2 },
		],
	},
	{
		ticket_type_label: "ワイド",
		ticket_type_name: "wide",
		payout_amount: 340,
		popularity: 2,
		horses: [
			{ horse_number: 3, sort_order: 1 },
			{ horse_number: 5, sort_order: 2 },
		],
	},
	{
		ticket_type_label: "ワイド",
		ticket_type_name: "wide",
		payout_amount: 280,
		popularity: 3,
		horses: [
			{ horse_number: 3, sort_order: 1 },
			{ horse_number: 8, sort_order: 2 },
		],
	},
	{
		ticket_type_label: "ワイド",
		ticket_type_name: "wide",
		payout_amount: 440,
		popularity: 5,
		horses: [
			{ horse_number: 5, sort_order: 1 },
			{ horse_number: 8, sort_order: 2 },
		],
	},
	{
		ticket_type_label: "三連複",
		ticket_type_name: "sanrenpuku",
		payout_amount: 2150,
		popularity: 4,
		horses: [
			{ horse_number: 3, sort_order: 1 },
			{ horse_number: 5, sort_order: 2 },
			{ horse_number: 8, sort_order: 3 },
		],
	},
	{
		ticket_type_label: "三連単",
		ticket_type_name: "sanrentan",
		payout_amount: 18000,
		popularity: 12,
		horses: [
			{ horse_number: 3, sort_order: 1 },
			{ horse_number: 5, sort_order: 2 },
			{ horse_number: 8, sort_order: 3 },
		],
	},
];

export const Default: Story = {
	name: "全券種表示",
	args: {
		race: {
			...baseRace,
			payouts: allPayouts,
		},
	},
};

export const ArrowNotation: Story = {
	name: "馬単・三連単の矢印表記確認",
	args: {
		race: {
			...baseRace,
			payouts: [
				{
					ticket_type_label: "馬単",
					ticket_type_name: "umatan",
					payout_amount: 2410,
					popularity: 3,
					horses: [
						{ horse_number: 3, sort_order: 1 },
						{ horse_number: 5, sort_order: 2 },
					],
				},
				{
					ticket_type_label: "三連単",
					ticket_type_name: "sanrentan",
					payout_amount: 18000,
					popularity: 12,
					horses: [
						{ horse_number: 3, sort_order: 1 },
						{ horse_number: 5, sort_order: 2 },
						{ horse_number: 8, sort_order: 3 },
					],
				},
			],
		},
	},
};
