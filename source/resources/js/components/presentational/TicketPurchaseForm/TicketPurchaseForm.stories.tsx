import type { Meta, StoryObj } from "@storybook/react";
import TicketPurchaseForm from ".";
import type { TicketPurchaseFormProps } from ".";

const meta: Meta<typeof TicketPurchaseForm> = {
	title: "TicketPurchaseForm",
	component: TicketPurchaseForm,
};

export default meta;
type Story = StoryObj<typeof TicketPurchaseForm>;

const baseArgs: TicketPurchaseFormProps = {
	selectedVenue: "東京",
	selectedRaceDate: "2026-04-05",
	selectedRaceNumber: 1,
	selectedTicketTypeId: "umaren",
	selectedBuyTypeId: "nagashi",
	selectedAxisCount: 1,
	selectedNagashiDirection: 1,
	selectedHorses: { axis: [3], others: [1, 5, 7] },
	amount: 100,
};

// ① 複数頭選択のみ
export const Tansho: Story = {
	name: "単勝・通常（①複数頭選択）",
	args: {
		...baseArgs,
		selectedTicketTypeId: "tansho",
		selectedBuyTypeId: "single",
		selectedHorses: { horses: [5] },
	},
};

export const Box: Story = {
	name: "馬連・ボックス（①複数頭選択）",
	args: {
		...baseArgs,
		selectedTicketTypeId: "umaren",
		selectedBuyTypeId: "box",
		selectedHorses: { horses: [1, 3, 5] },
	},
};

// ② 軸1頭 + 相手複数
export const UmarenNagashi: Story = {
	name: "馬連・流し（②軸1頭＋相手）",
	args: {
		...baseArgs,
		selectedTicketTypeId: "umaren",
		selectedBuyTypeId: "nagashi",
		selectedAxisCount: 1,
		selectedHorses: { axis: [3], others: [1, 5, 7] },
	},
};

// 枠連は枠番1〜8
export const WakurenNagashi: Story = {
	name: "枠連・流し（枠番1〜8グリッド）",
	args: {
		...baseArgs,
		selectedTicketTypeId: "wakuren",
		selectedBuyTypeId: "nagashi",
		selectedAxisCount: 1,
		selectedHorses: { axis: [2], others: [4, 6] },
	},
};

// ③ 軸2頭 + 相手複数（三連複のみ）
export const SanrenpukuNagashi1jiku: Story = {
	name: "三連複・流し・1頭軸（②軸1頭＋相手）",
	args: {
		...baseArgs,
		selectedTicketTypeId: "sanrenpuku",
		selectedBuyTypeId: "nagashi",
		selectedAxisCount: 1,
		selectedHorses: { axis: [3], others: [1, 5, 7, 9] },
	},
};

export const SanrenpukuNagashi2jiku: Story = {
	name: "三連複・流し・2頭軸（③軸2頭＋相手）",
	args: {
		...baseArgs,
		selectedTicketTypeId: "sanrenpuku",
		selectedBuyTypeId: "nagashi",
		selectedAxisCount: 2,
		selectedHorses: { axis1: [3], axis2: [5], others: [1, 7, 9] },
	},
};

// ④ 着順別に複数頭選択
export const SanrentanNagashi1chaku: Story = {
	name: "三連単・流し・1着流し（④着順別）",
	args: {
		...baseArgs,
		selectedTicketTypeId: "sanrentan",
		selectedBuyTypeId: "nagashi",
		selectedNagashiDirection: 1,
		selectedHorses: { col1: [3], col2: [1, 5, 7], col3: [1, 5, 7] },
	},
};

export const SanrentanNagashi2chaku: Story = {
	name: "三連単・流し・2着流し（④着順別）",
	args: {
		...baseArgs,
		selectedTicketTypeId: "sanrentan",
		selectedBuyTypeId: "nagashi",
		selectedNagashiDirection: 2,
		selectedHorses: { col1: [1, 5, 7], col2: [3], col3: [1, 5, 7] },
	},
};

export const SanrentanFormation: Story = {
	name: "三連単・フォーメーション（④着順別）",
	args: {
		...baseArgs,
		selectedTicketTypeId: "sanrentan",
		selectedBuyTypeId: "formation",
		selectedHorses: { col1: [1, 2], col2: [3, 4], col3: [5, 6, 7] },
	},
};
