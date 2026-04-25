import type { Meta, StoryObj } from "@storybook/react-vite";
import { RaceInfoSection } from ".";

const meta: Meta<typeof RaceInfoSection> = {
	title: "features/ticket/RaceInfoSection",
	component: RaceInfoSection,
};

export default meta;
type Story = StoryObj<typeof RaceInfoSection>;

export const Default: Story = {
	name: "デフォルト",
	args: {
		selectedVenue: "東京",
		selectedRaceDate: "2026-04-05",
		selectedRaceNumber: 1,
		onVenueChange: () => {},
		onRaceDateChange: () => {},
		onRaceNumberChange: () => {},
	},
};

export const SelectedRace5: Story = {
	name: "5レース選択済み",
	args: {
		selectedVenue: "阪神",
		selectedRaceDate: "2026-04-05",
		selectedRaceNumber: 5,
		onVenueChange: () => {},
		onRaceDateChange: () => {},
		onRaceNumberChange: () => {},
	},
};
