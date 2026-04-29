import type { Meta, StoryObj } from "@storybook/react-vite";
import HorseNoteModal from ".";

const meta: Meta<typeof HorseNoteModal> = {
	title: "features/horseNote/presentational/HorseNoteModal",
	component: HorseNoteModal,
	args: {
		open: true,
		horseName: "ディープスター",
		contentMaxLength: 1000,
		errorMessage: null,
		submitting: false,
		onContentChange: () => {},
		onRaceSelect: () => {},
		onOpenChange: () => {},
		onSubmit: () => {},
	},
};

export default meta;
type Story = StoryObj<typeof HorseNoteModal>;

const sampleRaceOptions = [
	{ id: 1, uid: "abc001", label: "2026/04/19 東京 11R 皐月賞" },
	{ id: 2, uid: "abc002", label: "2026/04/26 東京 9R 4歳上1勝クラス" },
	{ id: 3, uid: "abc003", label: "2026/05/03 京都 12R 4歳上2勝クラス" },
];

export const CreateFromRaceResultPage: Story = {
	name: "追加（レース結果画面 - レース固定）",
	args: {
		mode: "create",
		content: "",
		raceContext: {
			type: "fixed",
			label: "2026/04/19 東京 11R 皐月賞",
		},
	},
};

export const CreateFromHorseDetailPage: Story = {
	name: "追加（競走馬詳細画面 - レース選択可）",
	args: {
		mode: "create",
		content: "",
		raceContext: {
			type: "selectable",
			options: sampleRaceOptions,
			selectedUid: null,
		},
	},
};

export const CreateFromHorseDetailPageNoRace: Story = {
	name: "追加（競走馬詳細画面 - 該当レースなし）",
	args: {
		mode: "create",
		content: "",
		raceContext: {
			type: "none",
		},
	},
};

export const Edit: Story = {
	name: "編集（既存メモ表示）",
	args: {
		mode: "edit",
		content: "前走は外枠で出遅れ気味。今回は内枠で本命視できる。",
		raceContext: {
			type: "fixed",
			label: "2026/04/19 東京 11R 皐月賞",
		},
	},
};

export const NearLimit: Story = {
	name: "文字数カウンタ表示（上限近く）",
	args: {
		mode: "create",
		content: "あ".repeat(950),
		raceContext: {
			type: "fixed",
			label: "2026/04/19 東京 11R 皐月賞",
		},
	},
};

export const OverLimit: Story = {
	name: "文字数超過エラー（保存不可）",
	args: {
		mode: "create",
		content: "あ".repeat(1050),
		raceContext: {
			type: "fixed",
			label: "2026/04/19 東京 11R 皐月賞",
		},
	},
};

export const ServerError: Story = {
	name: "サーバーエラー表示",
	args: {
		mode: "create",
		content: "テストメモ",
		errorMessage: "同じレースに対するメモは既に存在します",
		raceContext: {
			type: "fixed",
			label: "2026/04/19 東京 11R 皐月賞",
		},
	},
};

export const Submitting: Story = {
	name: "送信中（ボタン無効）",
	args: {
		mode: "create",
		content: "テストメモ",
		submitting: true,
		raceContext: {
			type: "fixed",
			label: "2026/04/19 東京 11R 皐月賞",
		},
	},
};

export const Mobile: Story = {
	name: "モバイル表示",
	globals: {
		viewport: { value: "mobile1", isRotated: false },
	},
	args: {
		mode: "create",
		content: "",
		raceContext: {
			type: "fixed",
			label: "2026/04/19 東京 11R 皐月賞",
		},
	},
};
