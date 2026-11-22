import type { Meta, StoryObj } from "@storybook/react-vite";
import EntryCommentIconButton from ".";

const meta: Meta<typeof EntryCommentIconButton> = {
	title: "features/entryComment/presentational/EntryCommentIconButton",
	component: EntryCommentIconButton,
	args: {
		ariaLabel: "ディープスターの調教師・騎手コメント",
		onClick: () => {},
	},
};

export default meta;
type Story = StoryObj<typeof EntryCommentIconButton>;

export const HasComments: Story = {
	name: "コメントあり（件数バッジ）",
	args: { commentCount: 3 },
};

export const NoComments: Story = {
	name: "コメントなし（＋アイコン）",
	args: { commentCount: 0 },
};
