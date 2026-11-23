import type { Meta, StoryObj } from "@storybook/react-vite";
import type { EntryComment } from "../../types";
import CommentOutcomeList from ".";

const comments: EntryComment[] = [
	{
		id: 1,
		speaker_type: "trainer",
		speaker_id: 10,
		speaker_name: "加藤 征弘",
		sentiment: "positive",
		content: "1週前の追い切りで好時計。状態は過去最高",
		outcome: "hit",
		created_at: "2026-11-20T10:00:00+09:00",
	},
	{
		id: 2,
		speaker_type: "jockey",
		speaker_id: 20,
		speaker_name: "ルメール",
		sentiment: "conditional",
		content: "展開が向けば差し込める",
		outcome: null,
		created_at: "2026-11-21T10:00:00+09:00",
	},
];

const meta: Meta<typeof CommentOutcomeList> = {
	title: "features/entryComment/presentational/CommentOutcomeList",
	component: CommentOutcomeList,
	args: {
		pendingKeys: [],
		onOutcomeChange: () => {},
	},
};

export default meta;
type Story = StoryObj<typeof CommentOutcomeList>;

export const WithComments: Story = {
	name: "コメントあり",
	args: { comments },
};

export const Saving: Story = {
	name: "当否を保存中",
	args: { comments, pendingKeys: ["jockey-2"] },
};

export const NoComments: Story = {
	name: "コメントなし（-）",
	args: { comments: [] },
};

export const NoEntry: Story = {
	name: "出馬表データなし（-）",
	args: { comments: null },
};
