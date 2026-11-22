import type { Meta, StoryObj } from "@storybook/react-vite";
import RaceNominationAddForm from ".";

const meta: Meta<typeof RaceNominationAddForm> = {
	title: "features/raceNomination/presentational/RaceNominationAddForm",
	component: RaceNominationAddForm,
	args: {
		value: "",
		candidates: [],
		submitting: false,
		errorMessage: null,
		onValueChange: () => {},
		onCandidateSelect: () => {},
		onSubmit: () => {},
	},
};

export default meta;
type Story = StoryObj<typeof RaceNominationAddForm>;

export const Default: Story = {
	name: "初期状態",
};

export const WithCandidates: Story = {
	name: "候補表示中",
	args: {
		value: "ウワサ",
		candidates: [
			{ id: 1, name: "ウワサノホース", birth_year: 2022 },
			{ id: 2, name: "ウワサノホース", birth_year: null },
		],
	},
};

export const WithError: Story = {
	name: "エラー表示",
	args: {
		value: "ウワサノホース",
		errorMessage: "この馬は既に登録馬に追加されています。",
	},
};
