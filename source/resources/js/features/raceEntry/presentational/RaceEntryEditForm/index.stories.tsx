import type { Meta, StoryObj } from "@storybook/react-vite";
import RaceEntryEditForm from ".";
import type { RaceEntryEditFormProps } from ".";

const meta: Meta<typeof RaceEntryEditForm> = {
	title: "features/raceEntry/presentational/RaceEntryEditForm",
	component: RaceEntryEditForm,
};

export default meta;
type Story = StoryObj<typeof RaceEntryEditForm>;

const baseArgs: RaceEntryEditFormProps = {
	raceUid: "test-race-uid-123",
	raceInfo: {
		race_date: "2026-04-26",
		venue_name: "東京",
		race_number: 11,
	},
	values: {
		horse_name: "コントレイル",
		jockey_name: "福永祐一",
		frame_number: 2,
		horse_number: 3,
		weight: "57.0",
		horse_weight: "486",
	},
	errors: {},
	isSubmitting: false,
	onChange: () => {},
	onSubmit: () => {},
};

export const Default: Story = {
	name: "初期表示",
	args: {
		...baseArgs,
	},
};

export const Submitting: Story = {
	name: "送信中",
	args: {
		...baseArgs,
		isSubmitting: true,
	},
};

export const WithValidationErrors: Story = {
	name: "バリデーションエラー",
	args: {
		...baseArgs,
		values: {
			...baseArgs.values,
			horse_name: "",
			horse_number: 19,
		},
		errors: {
			horse_name: "馬名を入力してください",
			horse_number: "馬番は1〜18の範囲で入力してください",
		},
	},
};

export const EmptyHorseWeight: Story = {
	name: "馬体重未入力",
	args: {
		...baseArgs,
		values: {
			...baseArgs.values,
			horse_weight: "",
		},
	},
};

export const MobileView: Story = {
	name: "モバイル表示",
	globals: {
		viewport: { value: "mobile1", isRotated: false },
	},
	args: {
		...baseArgs,
	},
};
