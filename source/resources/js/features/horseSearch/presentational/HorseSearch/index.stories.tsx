import type { Meta, StoryObj } from "@storybook/react-vite";
import HorseSearch from ".";
import type { HorseSearchProps } from ".";

const meta: Meta<typeof HorseSearch> = {
	title: "features/horseSearch/presentational/HorseSearch",
	component: HorseSearch,
};

export default meta;
type Story = StoryObj<typeof HorseSearch>;

const sampleHorses: HorseSearchProps["horses"] = [
	{ uid: "horse001", name: "サンプルホース", birth_year: 2020 },
	{ uid: "horse002", name: "サンプルホースII", birth_year: 2021 },
	{ uid: "horse003", name: "サンプルホースIII", birth_year: null },
];

const noop = () => {};

export const Default: Story = {
	args: {
		horses: [],
		keyword: "",
		searchedKeyword: null,
		onKeywordChange: noop,
		onSearch: noop,
	},
};

export const WithResults: Story = {
	args: {
		horses: sampleHorses,
		keyword: "サンプル",
		searchedKeyword: "サンプル",
		onKeywordChange: noop,
		onSearch: noop,
	},
};

export const NoResults: Story = {
	args: {
		horses: [],
		keyword: "ミツカラナイ",
		searchedKeyword: "ミツカラナイ",
		onKeywordChange: noop,
		onSearch: noop,
	},
};
