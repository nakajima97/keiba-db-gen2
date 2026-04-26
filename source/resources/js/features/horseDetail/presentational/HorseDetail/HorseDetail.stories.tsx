import type { Meta, StoryObj } from "@storybook/react-vite";
import HorseDetail from ".";
import type { HorseDetailProps } from ".";

const meta: Meta<typeof HorseDetail> = {
	title: "features/horseDetail/presentational/HorseDetail",
	component: HorseDetail,
};

export default meta;
type Story = StoryObj<typeof HorseDetail>;

const sampleHorse: HorseDetailProps["horse"] = {
	id: 1,
	name: "サンプルホース",
	birth_year: 2020,
	race_histories: [
		{
			race_uid: "race001",
			race_date: "2026-04-19",
			venue_name: "東京",
			race_number: 1,
			race_name: "サンプルレース",
			finishing_order: 1,
			jockey_name: "騎手 一郎",
			popularity: 3,
		},
		{
			race_uid: "race002",
			race_date: "2026-03-08",
			venue_name: "阪神",
			race_number: 5,
			race_name: null,
			finishing_order: 4,
			jockey_name: "騎手 二郎",
			popularity: 1,
		},
		{
			race_uid: "race003",
			race_date: "2026-01-25",
			venue_name: "中山",
			race_number: 11,
			race_name: "ウィンターカップ",
			finishing_order: 2,
			jockey_name: "騎手 三郎",
			popularity: 5,
		},
	],
};

export const Default: Story = {
	name: "通常表示",
	args: {
		horse: sampleHorse,
	},
};

export const NoRaceHistories: Story = {
	name: "レース履歴なし",
	args: {
		horse: {
			...sampleHorse,
			race_histories: [],
		},
	},
};

export const MobileView: Story = {
	name: "モバイル表示",
	globals: {
		viewport: { value: "mobile1", isRotated: false },
	},
	args: {
		horse: sampleHorse,
	},
};
