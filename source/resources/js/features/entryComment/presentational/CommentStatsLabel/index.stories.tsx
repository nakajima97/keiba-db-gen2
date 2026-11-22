import type { Meta, StoryObj } from "@storybook/react-vite";
import CommentStatsLabel from ".";

const meta: Meta<typeof CommentStatsLabel> = {
	title: "features/entryComment/presentational/CommentStatsLabel",
	component: CommentStatsLabel,
};

export default meta;
type Story = StoryObj<typeof CommentStatsLabel>;

export const All: Story = {
	name: "3つの向きすべてに実績あり",
	args: {
		stats: {
			positive: { hit: 3, judged: 10 },
			conditional: { hit: 5, judged: 8 },
			negative: { hit: 4, judged: 4 },
		},
	},
};

export const Partial: Story = {
	name: "一部の向きのみ（0件の向きは非表示）",
	args: {
		stats: {
			positive: { hit: 2, judged: 3 },
			conditional: { hit: 0, judged: 0 },
			negative: { hit: 0, judged: 1 },
		},
	},
};

export const Empty: Story = {
	name: "判定済みなし（何も表示しない）",
	args: {
		stats: {
			positive: { hit: 0, judged: 0 },
			conditional: { hit: 0, judged: 0 },
			negative: { hit: 0, judged: 0 },
		},
	},
};
