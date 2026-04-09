import type { Meta, StoryObj } from "@storybook/react-vite";
import RaceResultForm from ".";
import type { RaceResultFormProps } from ".";

const meta: Meta<typeof RaceResultForm> = {
	title: "RaceResultForm",
	component: RaceResultForm,
};

export default meta;
type Story = StoryObj<typeof RaceResultForm>;

const baseArgs: Pick<
	RaceResultFormProps,
	| "venueName"
	| "raceDate"
	| "raceNumber"
	| "onPasteChange"
	| "onSubmit"
	| "isSubmitting"
> = {
	venueName: "東京",
	raceDate: "2026-04-08",
	raceNumber: 11,
	onPasteChange: () => {},
	onSubmit: () => {},
	isSubmitting: false,
};

export const Empty: Story = {
	name: "初期状態（未入力）",
	args: {
		...baseArgs,
		pasteValue: "",
		parseError: null,
	},
};

export const HasText: Story = {
	name: "テキスト入力済み",
	args: {
		...baseArgs,
		pasteValue: "単勝\t3\t610円\t2番人気",
		parseError: null,
	},
};

export const WithParseError: Story = {
	name: "フォーマットエラー",
	args: {
		...baseArgs,
		pasteValue: "不正なデータ形式のテキスト",
		parseError:
			"データの形式が認識できません。JRA公式サイトの払い戻し情報をコピーしてペーストしてください。",
	},
};

export const Submitting: Story = {
	name: "保存中",
	args: {
		...baseArgs,
		pasteValue: "単勝\t3\t610円\t2番人気",
		parseError: null,
		isSubmitting: true,
	},
};
