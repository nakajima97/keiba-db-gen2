import type { Meta, StoryObj } from "@storybook/react-vite";
import BackButton from ".";

const meta: Meta<typeof BackButton> = {
	title: "components/presentational/BackButton",
	component: BackButton,
};

export default meta;
type Story = StoryObj<typeof BackButton>;

export const WithHref: Story = {
	name: "リンク遷移（戻り先固定）",
	args: {
		label: "レース結果へ戻る",
		href: "/races/test-uid/result/edit",
	},
};

export const BrowserBack: Story = {
	name: "ブラウザback（戻り先動的）",
	args: {
		label: "戻る",
	},
};
