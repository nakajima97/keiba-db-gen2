import type { Meta, StoryObj } from "@storybook/react-vite";
import RaceResultForm from ".";
import type { RaceResultFormProps } from ".";

const meta: Meta<typeof RaceResultForm> = {
	title: "features/raceResult/presentational/RaceResultForm",
	component: RaceResultForm,
};

export default meta;
type Story = StoryObj<typeof RaceResultForm>;

const baseArgs: Pick<
	RaceResultFormProps,
	| "venueName"
	| "raceDate"
	| "raceNumber"
	| "onResultPasteChange"
	| "onPayoutPasteChange"
	| "onSubmit"
	| "isSubmitting"
> = {
	venueName: "東京",
	raceDate: "2026-04-08",
	raceNumber: 11,
	onResultPasteChange: () => {},
	onPayoutPasteChange: () => {},
	onSubmit: () => {},
	isSubmitting: false,
};

const resultMockData =
	"1\t枠8桃\t13\tリケアマカロニ\t牝3\t55.0\t横山 和生\t1:39.0\t\t\n12 11\n37.4\t474(+2)\t小野 次郎\t3\n2\t枠7橙\t12\tエリカビアリッツ\t牝3\t55.0\tD.レーン\t1:39.3\t２\t\n10 5\n38.5\t454(0)\t堀 宣行\t1";

const payoutMockData = "単勝\t3\t610円\t2番人気";

export const Empty: Story = {
	name: "初期状態（未入力）",
	args: {
		...baseArgs,
		resultPasteValue: "",
		resultParseError: null,
		payoutPasteValue: "",
		payoutParseError: null,
	},
};

export const ResultOnly: Story = {
	name: "着順のみ入力済み",
	args: {
		...baseArgs,
		resultPasteValue: resultMockData,
		resultParseError: null,
		payoutPasteValue: "",
		payoutParseError: null,
	},
};

export const PayoutOnly: Story = {
	name: "払い戻しのみ入力済み",
	args: {
		...baseArgs,
		resultPasteValue: "",
		resultParseError: null,
		payoutPasteValue: payoutMockData,
		payoutParseError: null,
	},
};

export const BothFilled: Story = {
	name: "両方入力済み",
	args: {
		...baseArgs,
		resultPasteValue: resultMockData,
		resultParseError: null,
		payoutPasteValue: payoutMockData,
		payoutParseError: null,
	},
};

export const WithResultParseError: Story = {
	name: "着順フォーマットエラー",
	args: {
		...baseArgs,
		resultPasteValue: "不正なデータ形式のテキスト",
		resultParseError:
			"データの形式が認識できません。JRA公式サイトの着順情報をコピーしてペーストしてください。",
		payoutPasteValue: "",
		payoutParseError: null,
	},
};

export const WithPayoutParseError: Story = {
	name: "払い戻しフォーマットエラー",
	args: {
		...baseArgs,
		resultPasteValue: resultMockData,
		resultParseError: null,
		payoutPasteValue: "不正なデータ形式のテキスト",
		payoutParseError:
			"データの形式が認識できません。JRA公式サイトの払い戻し情報をコピーしてペーストしてください。",
	},
};

export const Submitting: Story = {
	name: "保存中",
	args: {
		...baseArgs,
		resultPasteValue: resultMockData,
		resultParseError: null,
		payoutPasteValue: payoutMockData,
		payoutParseError: null,
		isSubmitting: true,
	},
};
