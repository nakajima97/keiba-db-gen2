import type { Meta, StoryObj } from "@storybook/react-vite";
import HorseNoteCell from ".";

const meta: Meta<typeof HorseNoteCell> = {
	title: "features/horseNote/presentational/HorseNoteCell",
	component: HorseNoteCell,
	args: {
		onClick: () => {},
	},
};

export default meta;
type Story = StoryObj<typeof HorseNoteCell>;

export const RaceLinkedNote: Story = {
	name: "そのレースに紐づくメモ（最優先で表示）",
	args: {
		content: "前走は外枠で出遅れ気味。今回は内枠で本命視できる。",
		source: "race",
	},
};

export const HorseLinkedNote: Story = {
	name: "レース紐づきなしのメモ（フォールバック表示）",
	args: {
		content:
			"次この条件だったら買いたい。芝1600mの稍重がベスト条件。\n併せ馬の動き◎",
		source: "horse",
	},
};

export const NoNote: Story = {
	name: "メモなし（追加ボタン表示）",
	args: {
		content: null,
		source: null,
	},
};

export const LongContent: Story = {
	name: "長文メモ（2行で省略）",
	args: {
		content:
			"前走は外枠から出遅れて末脚を活かせなかった。今回は内枠を引けたので、好位から流れに乗れれば一発の可能性がある。鞍上も継続騎乗で手の内に入っているはず。馬体重の増減にも注目。",
		source: "race",
	},
};
