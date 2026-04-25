import type { Meta, StoryObj } from "@storybook/react-vite";
import RaceDetail from ".";
import type { RaceDetailProps } from ".";

const meta: Meta<typeof RaceDetail> = {
	title: "features/raceDetail/presentational/RaceDetail",
	component: RaceDetail,
};

export default meta;
type Story = StoryObj<typeof RaceDetail>;

const sampleRace: RaceDetailProps["race"] = {
	uid: "abc001",
	race_date: "2026-04-19",
	venue_name: "東京",
	race_number: 1,
	entries: [
		{
			frame_number: 1,
			horse_number: 1,
			horse_name: "サンプルホース1",
			jockey_name: "騎手 一郎",
			weight: 480,
		},
		{
			frame_number: 1,
			horse_number: 2,
			horse_name: "サンプルホース2",
			jockey_name: "騎手 二郎",
			weight: 462,
		},
		{
			frame_number: 2,
			horse_number: 3,
			horse_name: "サンプルホース3",
			jockey_name: "騎手 三郎",
			weight: null,
		},
		{
			frame_number: 2,
			horse_number: 4,
			horse_name: "サンプルホース4",
			jockey_name: "騎手 四郎",
			weight: 510,
		},
	],
};

export const Default: Story = {
	name: "通常表示",
	args: {
		race: sampleRace,
	},
};

export const WeightMissing: Story = {
	name: "馬体重なしの馬を含む",
	args: {
		race: {
			...sampleRace,
			entries: sampleRace.entries.map((e) => ({ ...e, weight: null })),
		},
	},
};
