import type { Meta, StoryObj } from "@storybook/react-vite";
import HorseNoteDeleteConfirmDialog from ".";

const meta: Meta<typeof HorseNoteDeleteConfirmDialog> = {
	title: "features/horseNote/presentational/HorseNoteDeleteConfirmDialog",
	component: HorseNoteDeleteConfirmDialog,
	args: {
		open: true,
		onOpenChange: () => {},
		onConfirm: () => {},
	},
};

export default meta;
type Story = StoryObj<typeof HorseNoteDeleteConfirmDialog>;

export const Default: Story = {
	name: "通常状態",
	args: {
		noteContent:
			"次この条件だったら買いたい。芝1600mの稍重がベスト条件。",
		submitting: false,
		errorMessage: null,
	},
};

export const Submitting: Story = {
	name: "削除処理中（ボタンdisabled）",
	args: {
		noteContent:
			"次この条件だったら買いたい。芝1600mの稍重がベスト条件。",
		submitting: true,
		errorMessage: null,
	},
};

export const WithError: Story = {
	name: "削除失敗（エラー表示）",
	args: {
		noteContent:
			"次この条件だったら買いたい。芝1600mの稍重がベスト条件。",
		submitting: false,
		errorMessage: "メモの削除に失敗しました。時間をおいて再度お試しください。",
	},
};

export const LongContent: Story = {
	name: "メモ本文が長い場合（最大高さで縦スクロール）",
	args: {
		noteContent:
			"前走は外枠で出遅れ気味。次は内枠なら本命視。鞍上継続騎乗で前進期待。馬体重は前走比でマイナス4kg程度なら理想。直近の追い切り内容も上々で、状態面の不安は少ない。展開的には先行馬有利のトラックバイアスが想定されるため、好位差し脚質との相性も悪くない。距離適性は1600〜2000mで問題なし。馬場は良〜稍重が好走条件で、重・不良は割引が必要。鞍上騎手の今開催コース成績も上昇基調で、信頼感は高い。",
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
		noteContent:
			"次この条件だったら買いたい。芝1600mの稍重がベスト条件。",
		submitting: false,
		errorMessage: null,
	},
};
