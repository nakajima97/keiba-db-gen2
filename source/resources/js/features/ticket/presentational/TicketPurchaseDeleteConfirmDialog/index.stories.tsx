import type { Meta, StoryObj } from "@storybook/react-vite";
import TicketPurchaseDeleteConfirmDialog from ".";

const meta: Meta<typeof TicketPurchaseDeleteConfirmDialog> = {
	title: "features/ticket/presentational/TicketPurchaseDeleteConfirmDialog",
	component: TicketPurchaseDeleteConfirmDialog,
	args: {
		open: true,
		onOpenChange: () => {},
		onConfirm: () => {},
	},
};

export default meta;
type Story = StoryObj<typeof TicketPurchaseDeleteConfirmDialog>;

const baseTicketSummary = {
	raceDate: "2026-04-05",
	venueName: "東京",
	raceNumber: 1,
	ticketTypeLabel: "馬連",
};

export const Default: Story = {
	name: "通常状態",
	args: {
		ticketSummary: baseTicketSummary,
		submitting: false,
		errorMessage: null,
	},
};

export const Submitting: Story = {
	name: "削除処理中（ボタンdisabled）",
	args: {
		ticketSummary: baseTicketSummary,
		submitting: true,
		errorMessage: null,
	},
};

export const WithError: Story = {
	name: "削除失敗（エラー表示）",
	args: {
		ticketSummary: baseTicketSummary,
		submitting: false,
		errorMessage:
			"馬券の削除に失敗しました。時間をおいて再度お試しください。",
	},
};

export const MissingRaceInfo: Story = {
	name: "レース情報なし（race_uidがnullの馬券）",
	args: {
		ticketSummary: {
			raceDate: null,
			venueName: null,
			raceNumber: null,
			ticketTypeLabel: "複勝",
		},
		submitting: false,
		errorMessage: null,
	},
};

export const Mobile: Story = {
	name: "モバイル表示",
	globals: {
		viewport: { value: "mobile1", isRotated: false },
	},
	args: {
		ticketSummary: baseTicketSummary,
		submitting: false,
		errorMessage: null,
	},
};
