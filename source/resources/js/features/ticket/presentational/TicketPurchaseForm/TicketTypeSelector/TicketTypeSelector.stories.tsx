import type { Meta, StoryObj } from "@storybook/react-vite";
import { TicketTypeSelector } from ".";

const meta: Meta<typeof TicketTypeSelector> = {
	title: "features/ticket/TicketTypeSelector",
	component: TicketTypeSelector,
};

export default meta;
type Story = StoryObj<typeof TicketTypeSelector>;

export const Tansho: Story = {
	name: "単勝・通常",
	args: {
		selectedTicketTypeId: "tansho",
		selectedBuyTypeId: "single",
		onTicketTypeChange: () => {},
		onBuyTypeChange: () => {},
	},
};

export const UmarenNagashi: Story = {
	name: "馬連・流し",
	args: {
		selectedTicketTypeId: "umaren",
		selectedBuyTypeId: "nagashi",
		onTicketTypeChange: () => {},
		onBuyTypeChange: () => {},
	},
};

export const SanrenpukuFormation: Story = {
	name: "三連複・フォーメーション",
	args: {
		selectedTicketTypeId: "sanrenpuku",
		selectedBuyTypeId: "formation",
		onTicketTypeChange: () => {},
		onBuyTypeChange: () => {},
	},
};

export const SanrentanNagashi: Story = {
	name: "三連単・流し",
	args: {
		selectedTicketTypeId: "sanrentan",
		selectedBuyTypeId: "nagashi",
		onTicketTypeChange: () => {},
		onBuyTypeChange: () => {},
	},
};
