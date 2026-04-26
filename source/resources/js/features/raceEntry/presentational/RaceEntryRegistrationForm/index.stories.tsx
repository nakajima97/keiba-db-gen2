import type { Meta, StoryObj } from "@storybook/react-vite";
import RaceEntryRegistrationForm from ".";
import type { RaceEntryRegistrationFormProps } from ".";

const meta: Meta<typeof RaceEntryRegistrationForm> = {
	title: "features/raceEntry/presentational/RaceEntryRegistrationForm",
	component: RaceEntryRegistrationForm,
};

export default meta;
type Story = StoryObj<typeof RaceEntryRegistrationForm>;

const baseArgs: RaceEntryRegistrationFormProps = {
	raceInfo: {
		race_date: "2026-04-26",
		venue_name: "東京",
		race_number: 11,
	},
	pastedText: "",
	isSubmitting: false,
	onPastedTextChange: () => {},
	onSubmit: () => {},
};

export const Default: Story = {
	name: "初期状態",
	args: {
		...baseArgs,
	},
};

export const Submitting: Story = {
	name: "送信中",
	args: {
		...baseArgs,
		pastedText:
			"1\t1\tコントレイル\t福永祐一\t486\n2\t2\tグランアレグリア\t川田将雅\t470",
		isSubmitting: true,
	},
};

export const WithPastedText: Story = {
	name: "テキスト入力済み",
	args: {
		...baseArgs,
		pastedText:
			"1\t1\tコントレイル\t福永祐一\t486\n2\t2\tグランアレグリア\t川田将雅\t470\n3\t3\tフィエールマン\t池添謙一\t458\n4\t4\tクロノジェネシス\t北村友一\t462\n5\t5\tカレンブーケドール\t津村明秀\t448",
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
