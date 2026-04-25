import type { Meta, StoryObj } from "@storybook/react-vite";
import { HorseSelectionSection } from ".";

const meta: Meta<typeof HorseSelectionSection> = {
	title: "features/ticket/HorseSelectionSection",
	component: HorseSelectionSection,
};

export default meta;
type Story = StoryObj<typeof HorseSelectionSection>;

export const Tansho: Story = {
	name: "単勝・通常（複数頭選択）",
	args: {
		selectedTicketTypeId: "tansho",
		selectedBuyTypeId: "single",
		selectedAxisCount: 1,
		selectedNagashiDirection: 1,
		selectedHorses: { horses: [5] },
		onAxisCountChange: () => {},
		onNagashiDirectionChange: () => {},
		onHorsesChange: () => {},
	},
};

export const UmarenBox: Story = {
	name: "馬連・ボックス（複数頭選択）",
	args: {
		selectedTicketTypeId: "umaren",
		selectedBuyTypeId: "box",
		selectedAxisCount: 1,
		selectedNagashiDirection: 1,
		selectedHorses: { horses: [1, 3, 5] },
		onAxisCountChange: () => {},
		onNagashiDirectionChange: () => {},
		onHorsesChange: () => {},
	},
};

export const UmarenNagashi: Story = {
	name: "馬連・流し（軸1頭＋相手）",
	args: {
		selectedTicketTypeId: "umaren",
		selectedBuyTypeId: "nagashi",
		selectedAxisCount: 1,
		selectedNagashiDirection: 1,
		selectedHorses: { axis: [3], others: [1, 5, 7] },
		onAxisCountChange: () => {},
		onNagashiDirectionChange: () => {},
		onHorsesChange: () => {},
	},
};

export const SanrenpukuNagashi1jiku: Story = {
	name: "三連複・流し・1頭軸（軸頭数セレクター表示）",
	args: {
		selectedTicketTypeId: "sanrenpuku",
		selectedBuyTypeId: "nagashi",
		selectedAxisCount: 1,
		selectedNagashiDirection: 1,
		selectedHorses: { axis: [3], others: [1, 5, 7, 9] },
		onAxisCountChange: () => {},
		onNagashiDirectionChange: () => {},
		onHorsesChange: () => {},
	},
};

export const SanrenpukuNagashi2jiku: Story = {
	name: "三連複・流し・2頭軸（軸頭数セレクター表示）",
	args: {
		selectedTicketTypeId: "sanrenpuku",
		selectedBuyTypeId: "nagashi",
		selectedAxisCount: 2,
		selectedNagashiDirection: 1,
		selectedHorses: { axis1: [3], axis2: [5], others: [1, 7, 9] },
		onAxisCountChange: () => {},
		onNagashiDirectionChange: () => {},
		onHorsesChange: () => {},
	},
};

export const SanrentanNagashi: Story = {
	name: "三連単・流し（流し方向セレクター表示）",
	args: {
		selectedTicketTypeId: "sanrentan",
		selectedBuyTypeId: "nagashi",
		selectedAxisCount: 1,
		selectedNagashiDirection: 1,
		selectedHorses: { col1: [3], col2: [1, 5, 7], col3: [1, 5, 7] },
		onAxisCountChange: () => {},
		onNagashiDirectionChange: () => {},
		onHorsesChange: () => {},
	},
};

export const SanrentanFormation: Story = {
	name: "三連単・フォーメーション（着順別）",
	args: {
		selectedTicketTypeId: "sanrentan",
		selectedBuyTypeId: "formation",
		selectedAxisCount: 1,
		selectedNagashiDirection: 1,
		selectedHorses: { col1: [1, 2], col2: [3, 4], col3: [5, 6, 7] },
		onAxisCountChange: () => {},
		onNagashiDirectionChange: () => {},
		onHorsesChange: () => {},
	},
};
