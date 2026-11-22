import type { Meta, StoryObj } from "@storybook/react-vite";
import TrainerNoteModal from ".";

const meta: Meta<typeof TrainerNoteModal> = {
	title: "features/trainerNote/presentational/TrainerNoteModal",
	component: TrainerNoteModal,
	args: {
		open: true,
		trainerName: "加藤 征弘",
		trainerAffiliation: "美浦",
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
type Story = StoryObj<typeof TrainerNoteModal>;

export const Create: Story = {
	name: "追加（メモなし）",
	args: {
		mode: "create",
		content: "",
	},
};

export const Edit: Story = {
	name: "編集（メモあり）",
	args: {
		mode: "edit",
		content: "「状態は良い」は定型句。「今回は勝負」と言ったときだけ信用できる。",
	},
};

export const WithoutAffiliation: Story = {
	name: "所属なし",
	args: {
		mode: "create",
		trainerAffiliation: null,
		content: "",
	},
};

export const WithError: Story = {
	name: "エラー表示",
	args: {
		mode: "edit",
		content: "メモ",
		errorMessage: "メモの保存に失敗しました",
	},
};
