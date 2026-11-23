import type { Meta, StoryObj } from "@storybook/react-vite";
import type { EntryComment } from "../../types";
import EntryCommentModal from ".";

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

const meta: Meta<typeof EntryCommentModal> = {
	title: "features/entryComment/presentational/EntryCommentModal",
	component: EntryCommentModal,
	args: {
		open: true,
		horseName: "ディープスター",
		trainerName: "加藤 征弘",
		jockeyName: "ルメール",
		comments,
		editingKey: null,
		form: { speakerType: "trainer", sentiment: "positive", content: "", outcome: null },
		contentMaxLength: 1000,
		errorMessage: null,
		submitting: false,
		pendingKeys: [],
		onOpenChange: () => {},
		onFormChange: () => {},
		onSubmit: () => {},
		onEditClick: () => {},
		onCancelEdit: () => {},
		onDeleteClick: () => {},
		onOutcomeChange: () => {},
	},
};

export default meta;
type Story = StoryObj<typeof EntryCommentModal>;

export const Empty: Story = {
	name: "コメントなし（追加フォームのみ）",
	args: { comments: [] },
};

export const WithComments: Story = {
	name: "コメントあり",
};

export const Editing: Story = {
	name: "コメントを編集中",
	args: {
		editingKey: "jockey-2",
		form: {
			speakerType: "jockey",
			sentiment: "conditional",
			content: "展開が向けば差し込める",
			outcome: null,
		},
	},
};

export const WithoutTrainer: Story = {
	name: "調教師未登録（騎手のみ選択可）",
	args: {
		trainerName: null,
		comments: [],
		form: { speakerType: "jockey", sentiment: "positive", content: "", outcome: null },
	},
};

export const OutcomeSaving: Story = {
	name: "一覧の当否を保存中",
	args: { pendingKeys: ["jockey-2"] },
};

export const WithError: Story = {
	name: "エラー表示",
	args: {
		form: { speakerType: "trainer", sentiment: "negative", content: "今回は様子見", outcome: null },
		errorMessage: "コメントの保存に失敗しました",
	},
};
