import type { Meta, StoryObj } from "@storybook/react-vite";
import RaceInputForm from ".";

const meta: Meta<typeof RaceInputForm> = {
	title: "features/raceInput/RaceInputForm",
	component: RaceInputForm,
};

export default meta;
type Story = StoryObj<typeof RaceInputForm>;

const baseVenues = [
	{ id: 1, name: "東京" },
	{ id: 2, name: "中山" },
	{ id: 3, name: "阪神" },
	{ id: 4, name: "京都" },
	{ id: 5, name: "新潟" },
	{ id: 6, name: "福島" },
	{ id: 7, name: "小倉" },
	{ id: 8, name: "函館" },
	{ id: 9, name: "札幌" },
	{ id: 10, name: "中京" },
];

export const Default: Story = {
	name: "初期フォーム",
	args: {
		venues: baseVenues,
		onSubmit: () => {},
	},
};

export const AfterSave: Story = {
	name: "保存後（競馬場・日付・番号引き継ぎ）",
	args: {
		venues: baseVenues,
		initialVenueId: 2,
		initialRaceDate: "2026-04-18",
		initialRaceNumber: 5,
		onSubmit: () => {},
	},
};
