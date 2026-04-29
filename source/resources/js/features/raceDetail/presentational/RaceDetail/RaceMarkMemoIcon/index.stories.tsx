import type { Meta, StoryObj } from "@storybook/react-vite";
import RaceMarkMemoIcon from ".";

const meta: Meta<typeof RaceMarkMemoIcon> = {
	title:
		"features/raceDetail/presentational/RaceDetail/RaceMarkMemoIcon",
	component: RaceMarkMemoIcon,
	args: {
		ariaLabel: "ディープスター・スポーツ報知の印メモ",
		onClick: () => {},
	},
};

export default meta;
type Story = StoryObj<typeof RaceMarkMemoIcon>;

export const Add: Story = {
	name: "メモなし（＋アイコン・追加）",
	args: {
		state: "add",
	},
};

export const Edit: Story = {
	name: "メモあり（ノートアイコン＋ドットバッジ・編集）",
	args: {
		state: "edit",
	},
};
