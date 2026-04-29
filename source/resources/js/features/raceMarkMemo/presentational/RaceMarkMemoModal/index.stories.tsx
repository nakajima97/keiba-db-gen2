import type { Meta, StoryObj } from "@storybook/react-vite";
import RaceMarkMemoModal from ".";

const meta: Meta<typeof RaceMarkMemoModal> = {
	title: "features/raceMarkMemo/presentational/RaceMarkMemoModal",
	component: RaceMarkMemoModal,
	args: {
		open: true,
		horseName: "ディープスター",
		columnLabel: "スポーツ報知",
		contentMaxLength: 1000,
		errorMessage: null,
		submitting: false,
		onContentChange: () => {},
		onOpenChange: () => {},
		onSubmit: () => {},
		onDelete: () => {},
	},
};

export default meta;
type Story = StoryObj<typeof RaceMarkMemoModal>;

export const Create: Story = {
	name: "追加（メモなし・印あり）",
	args: {
		mode: "create",
		markValue: "◎",
		content: "",
	},
};

export const Edit: Story = {
	name: "編集（メモあり・印あり）",
	args: {
		mode: "edit",
		markValue: "○",
		content: "内枠先行で展開ハマる想定。馬場が渋ればさらに有利。",
	},
};

export const EditWithoutMark: Story = {
	name: "編集（メモあり・印なし=印を後から消したケース）",
	args: {
		mode: "edit",
		markValue: null,
		content: "前走時点では本命視されていた根拠メモ。今回印は消したが備忘録として残す。",
	},
};

export const Submitting: Story = {
	name: "送信中（ボタン無効）",
	args: {
		mode: "edit",
		markValue: "▲",
		content: "テストメモ",
		submitting: true,
	},
};

export const WithError: Story = {
	name: "サーバーエラー表示",
	args: {
		mode: "create",
		markValue: "◎",
		content: "テストメモ",
		errorMessage: "メモの保存に失敗しました。時間を置いて再度お試しください。",
	},
};

export const NearLimit: Story = {
	name: "文字数カウンタ表示（上限近く）",
	args: {
		mode: "create",
		markValue: "◎",
		content: "あ".repeat(950),
	},
};

export const OverLimit: Story = {
	name: "文字数超過エラー（保存不可）",
	args: {
		mode: "create",
		markValue: "◎",
		content: "あ".repeat(1050),
	},
};

export const NoColumnLabel: Story = {
	name: "予想者ラベル未設定",
	args: {
		mode: "create",
		columnLabel: "",
		markValue: "△",
		content: "",
	},
};

export const Mobile: Story = {
	name: "モバイル表示",
	globals: {
		viewport: { value: "mobile1", isRotated: false },
	},
	args: {
		mode: "edit",
		markValue: "◎",
		content: "内枠先行で展開ハマる想定。馬場が渋ればさらに有利。",
	},
};
