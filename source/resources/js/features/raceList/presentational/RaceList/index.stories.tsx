import type { Meta, StoryObj } from "@storybook/react-vite";
import RaceList from ".";
import type { RaceListProps } from ".";

const meta: Meta<typeof RaceList> = {
	title: "RaceList",
	component: RaceList,
};

export default meta;
type Story = StoryObj<typeof RaceList>;

const sampleVenues: RaceListProps["venues"] = [
	{ id: 1, name: "東京" },
	{ id: 2, name: "中山" },
	{ id: 3, name: "阪神" },
	{ id: 4, name: "京都" },
];

const sampleRaces: RaceListProps["races"] = [
	{
		uid: "abc001",
		race_date: "2026-04-05",
		venue_name: "東京",
		race_number: 1,
	},
	{
		uid: "abc002",
		race_date: "2026-04-05",
		venue_name: "東京",
		race_number: 5,
	},
	{
		uid: "abc003",
		race_date: "2026-04-05",
		venue_name: "東京",
		race_number: 11,
	},
	{
		uid: "abc004",
		race_date: "2026-04-05",
		venue_name: "中山",
		race_number: 3,
	},
	{
		uid: "abc005",
		race_date: "2026-04-06",
		venue_name: "阪神",
		race_number: 8,
	},
	{
		uid: "abc006",
		race_date: "2026-04-06",
		venue_name: "京都",
		race_number: 12,
	},
];

const baseArgs = {
	venues: sampleVenues,
	selectedDate: "",
	selectedVenueId: "all",
	onDateChange: () => {},
	onVenueChange: () => {},
};

export const Empty: Story = {
	name: "空の状態",
	args: {
		...baseArgs,
		races: [],
	},
};

export const WithData: Story = {
	name: "データあり（フィルタなし）",
	args: {
		...baseArgs,
		races: sampleRaces,
	},
};

export const FilteredByDate: Story = {
	name: "日付でフィルタ済み",
	args: {
		...baseArgs,
		races: sampleRaces.filter((r) => r.race_date === "2026-04-05"),
		selectedDate: "2026-04-05",
	},
};

export const FilteredByVenue: Story = {
	name: "開催場所でフィルタ済み",
	args: {
		...baseArgs,
		races: sampleRaces.filter((r) => r.venue_name === "東京"),
		selectedVenueId: "1",
	},
};

export const FilteredByDateAndVenue: Story = {
	name: "日付と開催場所でフィルタ済み",
	args: {
		...baseArgs,
		races: sampleRaces.filter(
			(r) => r.race_date === "2026-04-05" && r.venue_name === "東京",
		),
		selectedDate: "2026-04-05",
		selectedVenueId: "1",
	},
};
