import type { Meta, StoryObj } from "@storybook/react-vite";
import RaceMarkSelect from ".";

const meta: Meta<typeof RaceMarkSelect> = {
	title: "features/raceDetail/presentational/RaceDetail/RaceMarkSelect",
	component: RaceMarkSelect,
	args: {
		onChange: () => {},
	},
};

export default meta;
type Story = StoryObj<typeof RaceMarkSelect>;

export const Default: Story = {
	name: "デフォルト（印未選択）",
	args: {
		value: null,
	},
};

export const Selected: Story = {
	name: "印選択済み（◎）",
	args: {
		value: "◎",
	},
};

export const WithBottomRowContext: Story = {
	name: "テーブル最下行（ドロップダウンが上向きに開くべきシナリオ）",
	args: {
		value: null,
	},
	decorators: [
		(Story) => (
			<div
				style={{
					height: "120px",
					display: "flex",
					alignItems: "flex-end",
					overflow: "hidden",
					border: "1px solid #e2e8f0",
					padding: "8px",
				}}
			>
				<Story />
			</div>
		),
	],
};
