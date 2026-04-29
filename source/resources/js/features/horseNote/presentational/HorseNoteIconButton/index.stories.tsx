import type { Meta, StoryObj } from "@storybook/react-vite";
import HorseNoteIconButton from ".";

const meta: Meta<typeof HorseNoteIconButton> = {
	title: "features/horseNote/presentational/HorseNoteIconButton",
	component: HorseNoteIconButton,
	args: {
		ariaLabel: "ディープスターのメモ",
		onClick: () => {},
	},
};

export default meta;
type Story = StoryObj<typeof HorseNoteIconButton>;

export const HasNote: Story = {
	name: "メモあり（ノートアイコン アウトライン）",
	args: {
		hasNote: true,
	},
};

export const NoNote: Story = {
	name: "メモなし（＋アイコン）",
	args: {
		hasNote: false,
	},
};
